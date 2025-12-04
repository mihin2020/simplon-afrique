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
        Schema::table('evaluations', function (Blueprint $table) {
            $table->uuid('labellisation_step_id')->nullable()->after('evaluation_grid_id');
            $table->text('president_comment')->nullable()->after('general_comment');
            $table->string('president_decision')->nullable()->after('president_comment');
            $table->timestamp('president_validated_at')->nullable()->after('president_decision');

            $table->foreign('labellisation_step_id')
                ->references('id')
                ->on('labellisation_steps')
                ->nullOnDelete();
        });

        Schema::table('candidatures', function (Blueprint $table) {
            $table->decimal('admin_global_score', 5, 2)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['labellisation_step_id']);
            $table->dropColumn(['labellisation_step_id', 'president_comment', 'president_decision', 'president_validated_at']);
        });

        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn('admin_global_score');
        });
    }
};
