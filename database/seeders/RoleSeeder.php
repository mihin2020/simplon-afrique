<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrateur'],
            ['name' => 'admin', 'label' => 'Administrateur'],
            ['name' => 'formateur', 'label' => 'Formateur'],
            ['name' => 'jury', 'label' => 'Membre du jury'],
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(
                ['name' => $role['name']],
                ['label' => $role['label']]
            );
        }
    }
}
