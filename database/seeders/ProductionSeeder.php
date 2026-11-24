<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrNew([
            'email' => 'admin@minvu-nuble.cl',
        ]);

        if (! $user->exists) {
            $user->name = 'Administrador SEREMI';
            $user->password = 'Minvu.Nuble.2026';
            $user->email_verified_at = now();
        }

        $user->role = UserRole::SuperAdmin;
        $user->save();
    }
}
