<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\confirm;
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

    $user = User::create([
        'name' => $name,
        'email' => mb_strtolower($email),
        'password' => Hash::make($password),
    ]);

    $message = "User created: {$user->email} (ID: {$user->id})";

    if ($user->id === 1) {
        $message .= ' [admin]';
    }

    $this->info($message);

    return self::SUCCESS;
})->purpose('Create a new user account');

Artisan::command('user:list', function () {
    $headers = ['ID', 'Name', 'Email', 'Admin', 'Created'];
    $rows = User::all()->map(fn ($user) => [
        $user->id,
        $user->name,
        $user->email,
        $user->id === 1 ? 'Yes' : '—',
        $user->created_at->format('Y-m-d H:i'),
    ]);

    $this->table($headers, $rows);
})->purpose('List all registered users');

Artisan::command('user:admin {id : User ID to promote}', function () {
    $target = User::find((int) $this->argument('id'));

    if (! $target) {
        $this->error("User not found (ID: {$this->argument('id')}).");

        return self::FAILURE;
    }

    if ($target->id === 1) {
        $this->warn("User {$target->email} (ID: 1) is already an admin.");

        return self::SUCCESS;
    }

    $currentAdmin = User::find(1);

    if ($currentAdmin) {
        $overwrite = confirm(
            label: "User ID 1 already belongs to {$currentAdmin->email}. Swap IDs?",
            default: false,
        );

        if (! $overwrite) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }
    }

    DB::transaction(function () use ($target, $currentAdmin) {
        $newId = $target->id;

        if ($currentAdmin) {
            $currentAdmin->update(['id' => -1]);
        }

        $target->update(['id' => 1]);

        if ($currentAdmin) {
            $currentAdmin->update(['id' => $newId]);
        }
    });

    $this->info("User {$target->email} promoted to admin (now ID 1).");

    return self::SUCCESS;
})->purpose('Promote a user to admin (swap ID with current ID 1)');
