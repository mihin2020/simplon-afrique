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
        Schema::create('formateur_profile_organization', function (Blueprint $table) {
            $table->uuid('formateur_profile_id');
            $table->uuid('organization_id');
            $table->timestamps();

            $table->primary(['formateur_profile_id', 'organization_id']);

            $table->foreign('formateur_profile_id')
                ->references('id')
                ->on('formateurs_profiles')
                ->cascadeOnDelete();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formateur_profile_organization');
    }
};
