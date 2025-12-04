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
        Schema::table('juries', function (Blueprint $table) {
            $table->uuid('evaluation_grid_id')->nullable()->after('status');

            $table->foreign('evaluation_grid_id')
                ->references('id')
                ->on('evaluation_grids')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('juries', function (Blueprint $table) {
            $table->dropForeign(['evaluation_grid_id']);
            $table->dropColumn('evaluation_grid_id');
        });
    }
};
