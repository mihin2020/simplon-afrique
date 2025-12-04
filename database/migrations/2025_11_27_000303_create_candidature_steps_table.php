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
        Schema::create('candidature_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('candidature_id');
            $table->uuid('labellisation_step_id');

            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->foreign('candidature_id')
                ->references('id')
                ->on('candidatures')
                ->cascadeOnDelete();

            $table->foreign('labellisation_step_id')
                ->references('id')
                ->on('labellisation_steps')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidature_steps');
    }
};
