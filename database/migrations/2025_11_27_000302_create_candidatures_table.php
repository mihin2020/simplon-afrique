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
        Schema::create('candidatures', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->uuid('current_step_id')->nullable();
            $table->uuid('badge_id')->nullable();

            $table->string('status')->default('draft'); // draft, submitted, in_review, validated, rejected

            $table->string('cv_path');
            $table->string('motivation_letter_path');
            $table->string('portfolio_url')->nullable();

            // JSON list of additional attachments (attestations & certifications files)
            $table->json('attachments')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('current_step_id')
                ->references('id')
                ->on('labellisation_steps')
                ->nullOnDelete();

            $table->foreign('badge_id')
                ->references('id')
                ->on('badges')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
