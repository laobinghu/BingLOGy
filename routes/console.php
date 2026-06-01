<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use App\Models\User;

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

    $passwordConfirmation = password(
        label: 'Confirm password',
        required: 'Password confirmation is required.',
        validate: fn (string $value): ?string => $value !== $password
            ? 'Passwords do not match.'
            : null,
    );

    unset($passwordConfirmation);

    if (! confirm(label: 'Create this user?', default: true)) {
        $this->warn('Cancelled.');

        return self::SUCCESS;
    }

    $user = User::create([
        'name' => $name,
        'email' => mb_strtolower($email),
        'password' => Hash::make($password),
    ]);

    $this->info("User created: {$user->email} (ID: {$user->id})");

    return self::SUCCESS;
})->purpose('Create a new user account via interactive prompts');
