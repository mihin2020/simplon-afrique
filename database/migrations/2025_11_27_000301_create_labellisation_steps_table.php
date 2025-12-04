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
        Schema::create('labellisation_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // internal: candidature, technique, pedagogique, entretien_evaluation, certification
            $table->string('label'); // human readable label
            $table->unsignedTinyInteger('display_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labellisation_steps');
    }
};
