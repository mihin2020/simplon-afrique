<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrer les données existantes de organization_id vers la table pivot
        $profiles = DB::table('formateurs_profiles')
            ->whereNotNull('organization_id')
            ->get();

        foreach ($profiles as $profile) {
            DB::table('formateur_profile_organization')->insert([
                'formateur_profile_id' => $profile->id,
                'organization_id' => $profile->organization_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer toutes les entrées de la table pivot
        DB::table('formateur_profile_organization')->truncate();
    }
};
