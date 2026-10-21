<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(GroupSeeder::class);

        // Get units & roles
        $legalUnit = Unit::where('name', 'Legal')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $pengelolRole = Role::where('name', 'Pengelola')->first();
        $pengunaRole = Role::where('name', 'Pengguna')->first();

        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password_hash' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'status' => \App\Enums\UserStatus::Active,
            ]
        );

        // Pengelola user
        User::firstOrCreate(
            ['email' => 'pengelola.legal@gmail.com'],
            [
                'name' => 'Pengelola Legal',
                'password_hash' => \Illuminate\Support\Facades\Hash::make('pengelola123'),
                'role_id' => $pengelolRole->id,
                'unit_id' => $legalUnit?->id,
                'status' => \App\Enums\UserStatus::Active,
            ]
        );

        // Pengguna user
        User::firstOrCreate(
            ['email' => 'pengguna@gmail.com'],
            [
                'name' => 'Pengguna',
                'password_hash' => \Illuminate\Support\Facades\Hash::make('pengguna123'),
                'role_id' => $pengunaRole->id,
                'unit_id' => $legalUnit?->id,
                'status' => \App\Enums\UserStatus::Active,
            ]
        );

        $this->call(UserGroupSeeder::class);
    }
}
