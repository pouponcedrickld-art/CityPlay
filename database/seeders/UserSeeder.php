<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles si nécessaire
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'joueur']);

        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'pseudo' => 'admin',
            'password' => Hash::make('password'),
            'consent_cgu' => true,
            'consent_donnees' => true,
            'is_admin' => true,
            'otp_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);

        User::firstOrCreate([
            'email' => 'joueur@example.com',
        ], [
            'name' => 'Joueur',
            'pseudo' => 'joueur',
            'password' => Hash::make('password'),
            'consent_cgu' => true,
            'consent_donnees' => true,
            'is_admin' => false,
            'otp_verified_at' => now(),
        ]);
    }
}
