<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('badge_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('badge_id')->constrained()->onDelete('cascade');
            $table->string('image_path')->nullable(); // Chemin de l'image du badge
            $table->timestamps();
        });

        // Table pour les paramètres d'attestation
        Schema::create('attestation_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('director_name')->nullable();
            $table->string('director_title')->nullable();
            $table->string('signature_path')->nullable(); // Signature du directeur
            $table->string('logo_path')->nullable(); // Logo Simplon
            $table->string('organization_name')->default('Simplon Africa');
            $table->text('attestation_text')->nullable(); // Texte personnalisable
            $table->timestamps();
        });

        // Ajouter colonnes à la table candidatures pour stocker le badge attribué et l'attestation
        Schema::table('candidatures', function (Blueprint $table) {
            $table->timestamp('badge_awarded_at')->nullable()->after('badge_id');
            $table->string('attestation_path')->nullable()->after('badge_awarded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['badge_awarded_at', 'attestation_path']);
        });

        Schema::dropIfExists('attestation_settings');
        Schema::dropIfExists('badge_configurations');
    }
};
