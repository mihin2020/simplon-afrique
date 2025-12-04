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
        Schema::create('evaluation_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('evaluation_grid_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('display_order')->default(0);

            $table->timestamps();

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
        Schema::dropIfExists('evaluation_categories');
    }
};
