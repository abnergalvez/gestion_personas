<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@minvu-nuble.cl'],
            [
                'name' => 'Administrador SEREMI',
                'password' => 'Minvu.Nuble.2026',
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'gestor@minvu-nuble.cl'],
            [
                'name' => 'Gestor de personal',
                'password' => 'Gestor.Nuble.2026',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );
    }
}
