<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if (! $superAdminRole) {
            $this->command->error('Le rôle super_admin n\'existe pas. Exécutez d\'abord RoleSeeder.');

            return;
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@simplon.africa'],
            [
                'name' => 'Super Administrateur',
                'password' => Hash::make('password'), // À changer en production !
            ]
        );

        // Attacher le rôle super_admin s'il ne l'a pas déjà
        if (! $superAdmin->roles()->where('roles.id', $superAdminRole->id)->exists()) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }
    }
}
