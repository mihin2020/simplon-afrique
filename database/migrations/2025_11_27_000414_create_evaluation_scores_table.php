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
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('evaluation_id');
            $table->uuid('evaluation_criterion_id');
            $table->decimal('raw_score', 5, 2);      // note sur 20
            $table->decimal('weighted_score', 6, 3); // poids * note
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->foreign('evaluation_id')
                ->references('id')
                ->on('evaluations')
                ->cascadeOnDelete();

            $table->foreign('evaluation_criterion_id')
                ->references('id')
                ->on('evaluation_criteria')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};
