<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('SEED_SUPERADMIN_NAME', 'Super Administrador');
        $email = env('SEED_SUPERADMIN_EMAIL', 'superadmin@agenda.test');
        $password = env('SEED_SUPERADMIN_PASSWORD', 'admin12345');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'estado' => 1,
            ]
        );

        $user->syncRoles(['SuperAdministrador']);
    }
}
