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
        Schema::table('evaluation_categories', function (Blueprint $table) {
            $table->uuid('labellisation_step_id')->nullable()->after('evaluation_grid_id');

            $table->foreign('labellisation_step_id')
                ->references('id')
                ->on('labellisation_steps')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_categories', function (Blueprint $table) {
            $table->dropForeign(['labellisation_step_id']);
            $table->dropColumn('labellisation_step_id');
        });
    }
};
