<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:create', function () {
    $name = text(
        label: 'Name',
        required: 'Name is required.',
    );

    $email = text(
        label: 'Email',
        required: 'Email is required.',
        validate: function (string $value): ?string {
            $validator = Validator::make(
                ['email' => $value],
                ['email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email']],
            );

            return $validator->fails() ? $validator->errors()->first('email') : null;
        },
    );

    $password = password(
        label: 'Password',
        required: 'Password is required.',
        validate: fn (string $value): ?string => match (true) {
            strlen($value) < 8 => 'Password must be at least 8 characters.',
            default => null,
        },
    );

    $availableRoles = Role::query()->orderBy('name')->pluck('name')->all();
    $roleNames = $availableRoles !== []
        ? multiselect(
            label: 'Assign roles (space to toggle, enter to confirm)',
            options: $availableRoles,
            default: [],
        )
        : [];

    $user = User::create([
        'name' => $name,
        'email' => mb_strtolower($email),
        'password' => Hash::make($password),
    ]);

    foreach ($roleNames as $role) {
        $user->assignRole($role);
    }

    $summary = "User created: {$user->email} (ID: {$user->id})";
    if ($roleNames !== []) {
        $summary .= ' [roles: '.implode(', ', $roleNames).']';
    }
    $this->info($summary);

    return self::SUCCESS;
})->purpose('Create a new user account (optionally assign roles)');

Artisan::command('user:list {--search= : Search by name or email} {--role= : Filter by role name} {--verified= : Filter by email verification: yes|no} {--2fa= : Filter by 2FA enabled: yes|no} {--sort=id : Sort column: id|name|email|created_at} {--direction=desc : Sort direction: asc|desc} {--per-page=20 : Users per page} {--page=1 : Page number}', function (): int {
    $search = (string) $this->option('search');
    $roleFilter = $this->option('role');
    $verified = $this->option('verified');
    $twoFactor = $this->option('2fa');
    $sort = (string) $this->option('sort');
    $direction = strtolower((string) $this->option('direction')) === 'asc' ? 'asc' : 'desc';
    $perPage = max(1, (int) $this->option('per-page'));
    $page = max(1, (int) $this->option('page'));

    $allowedSorts = ['id', 'name', 'email', 'created_at'];
    if (! in_array($sort, $allowedSorts, true)) {
        $this->error('Invalid --sort. Allowed: '.implode(', ', $allowedSorts));

        return self::FAILURE;
    }

    $query = User::query()->with('roles');

    if ($search !== '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    if ($roleFilter !== null && $roleFilter !== '') {
        $query->whereHas('roles', fn (Builder $q) => $q->where('name', $roleFilter));
    }

    if ($verified === 'yes') {
        $query->whereNotNull('email_verified_at');
    } elseif ($verified === 'no') {
        $query->whereNull('email_verified_at');
    }

    if ($twoFactor === 'yes') {
        $query->whereNotNull('two_factor_confirmed_at');
    } elseif ($twoFactor === 'no') {
        $query->whereNull('two_factor_confirmed_at');
    }

    $query->orderBy($sort, $direction);

    $total = (clone $query)->count();
    $users = $query->forPage($page, $perPage)->get();

    $headers = ['ID', 'Name', 'Email', 'Roles', 'Verified', '2FA', 'Created'];
    $rows = $users->map(fn (User $user) => [
        $user->id,
        $user->name,
        $user->email,
        $user->roles->pluck('name')->implode(', ') ?: '—',
        $user->email_verified_at ? 'Yes' : 'No',
        $user->two_factor_confirmed_at ? 'Yes' : 'No',
        $user->created_at->format('Y-m-d H:i'),
    ]);

    $this->table($headers, $rows);

    $lastPage = max(1, (int) ceil($total / $perPage));
    $this->line(sprintf(
        'Showing %d-%d of %d  (page %d/%d)',
        $users->count() === 0 ? 0 : ($page - 1) * $perPage + 1,
        ($page - 1) * $perPage + $users->count(),
        $total,
        $page,
        $lastPage,
    ));

    return self::SUCCESS;
})->purpose('List users with search, filter, sort and pagination');

Artisan::command('user:show {id : User ID}', function () {
    $user = User::with('roles')->find((int) $this->argument('id'));

    if (! $user) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    $rows = [
        ['ID', (string) $user->id],
        ['Name', $user->name],
        ['Email', $user->email],
        ['Roles', $user->roles->pluck('name')->implode(', ') ?: '—'],
        ['Verified', $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i') : 'No'],
        ['2FA', $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('Y-m-d H:i') : 'No'],
        ['Created', $user->created_at->format('Y-m-d H:i')],
        ['Updated', $user->updated_at->format('Y-m-d H:i')],
    ];

    $this->table(['Field', 'Value'], $rows);

    return self::SUCCESS;
})->purpose('Show detailed information for a user');

Artisan::command('user:update {id : User ID}', function () {
    $user = User::find((int) $this->argument('id'));

    if (! $user) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    $name = text(
        label: 'Name',
        default: $user->name,
        required: 'Name is required.',
    );

    $email = text(
        label: 'Email',
        default: $user->email,
        required: 'Email is required.',
        validate: function (string $value) use ($user): ?string {
            $validator = Validator::make(
                ['email' => $value],
                ['email' => ['required', 'email:rfc,dns', 'max:255', "unique:users,email,{$user->id}"]],
            );

            return $validator->fails() ? $validator->errors()->first('email') : null;
        },
    );

    $user->update([
        'name' => $name,
        'email' => mb_strtolower($email),
    ]);

    $this->info("User updated: {$user->email} (ID: {$user->id})");

    return self::SUCCESS;
})->purpose('Update a user\'s name and email');

Artisan::command('user:delete {id : User ID} {--force : Skip confirmation}', function () {
    $user = User::find((int) $this->argument('id'));

    if (! $user) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    if (! $this->option('force')) {
        $confirmed = confirm(
            label: "Delete user {$user->email} (ID: {$user->id})? This cannot be undone.",
            default: false,
        );

        if (! $confirmed) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    $email = $user->email;
    $user->delete();
    $this->info("User deleted: {$email}");

    return self::SUCCESS;
})->purpose('Delete a user account');

Artisan::command('user:reset-password {id : User ID} {--force : Skip confirmation}', function () {
    $user = User::find((int) $this->argument('id'));

    if (! $user) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    if (! $this->option('force')) {
        $confirmed = confirm(
            label: "Reset password for {$user->email} (ID: {$user->id})?",
            default: false,
        );

        if (! $confirmed) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    $newPassword = password(
        label: 'New password',
        required: 'Password is required.',
        validate: fn (string $value): ?string => match (true) {
            strlen($value) < 8 => 'Password must be at least 8 characters.',
            default => null,
        },
    );

    $user->forceFill(['password' => Hash::make($newPassword)])->save();
    $user->tokens()->delete();

    $this->info("Password reset for: {$user->email} (ID: {$user->id})");

    return self::SUCCESS;
})->purpose('Reset a user\'s password and revoke API tokens');

Artisan::command('user:verify-email {id : User ID} {--force : Skip confirmation}', function () {
    $user = User::find((int) $this->argument('id'));

    if (! $user) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    if ($user->email_verified_at) {
        $this->warn("Email already verified at {$user->email_verified_at->format('Y-m-d H:i')}.");

        return self::SUCCESS;
    }

    if (! $this->option('force')) {
        $confirmed = confirm(
            label: "Manually verify email for {$user->email} (ID: {$user->id})?",
            default: false,
        );

        if (! $confirmed) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    $user->forceFill(['email_verified_at' => now()])->save();
    $this->info("Email verified: {$user->email} (ID: {$user->id})");

    return self::SUCCESS;
})->purpose('Manually mark a user\'s email as verified');

Artisan::command('user:role {action : assign|remove|list} {id? : User ID (required for assign/remove)} {role? : Role name (required for assign/remove)}', function () {
    $action = (string) $this->argument('action');

    if ($action === 'list') {
        $headers = ['ID', 'Name', 'Label', 'Users'];
        $rows = Role::query()->withCount('users')->orderBy('name')->get()->map(fn (Role $role) => [
            $role->id,
            $role->name,
            (string) ($role->label ?? '—'),
            (string) $role->users_count,
        ]);
        $this->table($headers, $rows);

        return self::SUCCESS;
    }

    if (! in_array($action, ['assign', 'remove'], true)) {
        $this->error("Unknown action '{$action}'. Use: assign, remove, list.");

        return self::FAILURE;
    }

    $userId = $this->argument('id');
    $roleName = $this->argument('role');

    if ($userId === null || $roleName === null) {
        $this->error('Both <id> and <role> are required for assign/remove.');

        return self::FAILURE;
    }

    $user = User::find((int) $userId);
    if (! $user) {
        $this->error("User not found (ID: {$userId}).");

        return self::FAILURE;
    }

    if ($action === 'remove' && $roleName === 'admin' && Role::where('name', 'admin')->whereHas('users')->count() <= 1) {
        $this->error('Cannot remove the last admin. Promote another user first.');

        return self::FAILURE;
    }

    if ($action === 'assign') {
        $user->assignRole($roleName);
        $this->info("Assigned '{$roleName}' to {$user->email} (ID: {$user->id}).");
    } else {
        if (! $user->hasRole($roleName)) {
            $this->warn("User {$user->email} does not have role '{$roleName}'.");

            return self::SUCCESS;
        }
        $user->removeRole($roleName);
        $this->info("Removed '{$roleName}' from {$user->email} (ID: {$user->id}).");
    }

    return self::SUCCESS;
})->purpose('Manage user roles: assign, remove, or list roles');

Artisan::command('user:bulk-delete {--ids=* : Comma-separated user IDs} {--role= : Delete all users with this role (cannot be admin)} {--force : Skip confirmation}', function () {
    $ids = (array) $this->option('ids');
    $roleFilter = $this->option('role');

    if ($ids === [] && ! $roleFilter) {
        $this->error('Provide --ids or --role.');

        return self::FAILURE;
    }

    $query = User::query();
    if ($ids !== []) {
        $normalized = collect($ids)
            ->flatMap(fn ($raw) => is_string($raw) ? explode(',', $raw) : [$raw])
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $query->whereIn('id', $normalized);
    }

    if ($roleFilter) {
        if ($roleFilter === 'admin') {
            $this->error('Refusing to bulk-delete users with the admin role. Revoke admin first.');

            return self::FAILURE;
        }
        $query->whereHas('roles', fn (Builder $q) => $q->where('name', $roleFilter));
    }

    $users = $query->orderBy('id')->get();

    if ($users->isEmpty()) {
        $this->warn('No users matched.');

        return self::SUCCESS;
    }

    $this->table(['ID', 'Name', 'Email', 'Roles'], $users->map(fn (User $u) => [
        $u->id,
        $u->name,
        $u->email,
        $u->roles->pluck('name')->implode(', ') ?: '—',
    ]));

    if (! $this->option('force')) {
        $confirmed = confirm(
            label: "Delete {$users->count()} user(s)? This cannot be undone.",
            default: false,
        );

        if (! $confirmed) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    $count = 0;
    foreach ($users as $user) {
        $user->delete();
        $count++;
    }

    $this->info("Deleted {$count} user(s).");

    return self::SUCCESS;
})->purpose('Bulk delete users by IDs or role');

Artisan::command('user:bulk-role {action : assign|remove} {role : Role name} {--ids=* : Comma-separated user IDs} {--force : Skip confirmation}', function () {
    $action = (string) $this->argument('action');
    $roleName = (string) $this->argument('role');
    $ids = (array) $this->option('ids');

    if (! in_array($action, ['assign', 'remove'], true)) {
        $this->error("Unknown action '{$action}'. Use: assign or remove.");

        return self::FAILURE;
    }

    if ($ids === []) {
        $this->error('Provide at least one --ids.');

        return self::FAILURE;
    }

    $normalized = collect($ids)
        ->flatMap(fn ($raw) => is_string($raw) ? explode(',', $raw) : [$raw])
        ->map(fn ($v) => (int) trim((string) $v))
        ->filter(fn ($v) => $v > 0)
        ->unique()
        ->values()
        ->all();

    $users = User::whereIn('id', $normalized)->orderBy('id')->get();

    if ($users->isEmpty()) {
        $this->warn('No users matched.');

        return self::SUCCESS;
    }

    if ($action === 'remove' && $roleName === 'admin') {
        $remaining = User::whereHas('roles', fn (Builder $q) => $q->where('name', 'admin'))
            ->whereNotIn('id', $users->pluck('id'))
            ->count();
        if ($remaining === 0) {
            $this->error('Refusing to remove the last admin from the system.');

            return self::FAILURE;
        }
    }

    $this->table(['ID', 'Name', 'Email', 'Current Roles'], $users->map(fn (User $u) => [
        $u->id,
        $u->name,
        $u->email,
        $u->roles->pluck('name')->implode(', ') ?: '—',
    ]));

    if (! $this->option('force')) {
        $confirmed = confirm(
            label: "{$action} role '{$roleName}' on {$users->count()} user(s)?",
            default: false,
        );

        if (! $confirmed) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    $count = 0;
    foreach ($users as $user) {
        if ($action === 'assign') {
            $user->assignRole($roleName);
        } else {
            $user->removeRole($roleName);
        }
        $count++;
    }

    $this->info("{$action}ed '{$roleName}' on {$count} user(s).");

    return self::SUCCESS;
})->purpose('Bulk assign or remove a role for many users');

Artisan::command('user:export {--format=table : Output format: table|json|csv} {--output= : Write to file instead of stdout}', function () {
    $format = (string) $this->option('format');
    if (! in_array($format, ['table', 'json', 'csv'], true)) {
        $this->error('Invalid --format. Allowed: table, json, csv.');

        return self::FAILURE;
    }

    $users = User::with('roles')->orderBy('id')->get();
    $payload = $users->map(fn (User $u) => [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'roles' => $u->roles->pluck('name')->all(),
        'email_verified_at' => optional($u->email_verified_at)->toIso8601String(),
        'two_factor_confirmed_at' => optional($u->two_factor_confirmed_at)->toIso8601String(),
        'created_at' => $u->created_at->toIso8601String(),
        'updated_at' => $u->updated_at->toIso8601String(),
    ]);

    $content = match ($format) {
        'json' => $payload->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        'csv' => users_to_csv($payload),
        default => null,
    };

    if ($format === 'table') {
        $this->table(
            ['ID', 'Name', 'Email', 'Roles', 'Verified', '2FA', 'Created'],
            $users->map(fn (User $u) => [
                $u->id,
                $u->name,
                $u->email,
                $u->roles->pluck('name')->implode(', ') ?: '—',
                $u->email_verified_at ? 'Yes' : 'No',
                $u->two_factor_confirmed_at ? 'Yes' : 'No',
                $u->created_at->format('Y-m-d H:i'),
            ]),
        );
        $this->line("Total: {$users->count()} user(s).");
    } else {
        $output = $this->option('output');
        if ($output) {
            file_put_contents($output, $content);
            $this->info("Exported {$users->count()} user(s) to {$output}.");
        } else {
            $this->line($content);
        }
    }

    return self::SUCCESS;
})->purpose('Export users to table, JSON, or CSV');

Artisan::command('user:roles', function () {
    $headers = ['ID', 'Name', 'Label', 'Users'];
    $rows = Role::query()->withCount('users')->orderBy('name')->get()->map(fn (Role $role) => [
        $role->id,
        $role->name,
        (string) ($role->label ?? '—'),
        (string) $role->users_count,
    ]);

    $this->table($headers, $rows);

    return self::SUCCESS;
})->purpose('List all roles and how many users hold them');

if (! function_exists('users_to_csv')) {
    function users_to_csv(Collection $payload): string
    {
        if ($payload->isEmpty()) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_keys($payload->first()));

        foreach ($payload as $row) {
            $row['roles'] = implode('|', $row['roles']);
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return (string) $csv;
    }
}
