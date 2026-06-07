<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'editor', 'label' => 'Editor'],
            ['name' => 'user', 'label' => 'User'],
        ];

        foreach ($defaults as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $firstUser = User::orderBy('id')->first();
        if ($firstUser) {
            $firstUser->assignRole('admin');
        }
    }
}
