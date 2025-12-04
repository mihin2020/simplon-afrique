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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('candidature_id');
            $table->uuid('jury_id');
            $table->uuid('jury_member_id');
            $table->uuid('evaluation_grid_id');
            $table->string('status')->default('in_progress'); // in_progress, submitted, locked
            $table->text('general_comment')->nullable();
            $table->decimal('final_score', 6, 3)->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->foreign('candidature_id')
                ->references('id')
                ->on('candidatures')
                ->cascadeOnDelete();

            $table->foreign('jury_id')
                ->references('id')
                ->on('juries')
                ->cascadeOnDelete();

            $table->foreign('jury_member_id')
                ->references('id')
                ->on('jury_members')
                ->cascadeOnDelete();

            $table->foreign('evaluation_grid_id')
                ->references('id')
                ->on('evaluation_grids')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
