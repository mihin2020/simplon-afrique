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
        Schema::create('certification_formateur', function (Blueprint $table) {
            $table->uuid('formateur_profile_id');
            $table->uuid('certification_tag_id');
            $table->timestamps();

            $table->primary(['formateur_profile_id', 'certification_tag_id']);

            $table->foreign('formateur_profile_id')
                ->references('id')
                ->on('formateurs_profiles')
                ->cascadeOnDelete();

            $table->foreign('certification_tag_id')
                ->references('id')
                ->on('certifications_tags')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_formateur');
    }
};
