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
        Schema::create('badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // internal name: junior, intermediaire, senior
            $table->string('label'); // human readable
            $table->decimal('min_score', 5, 2)->nullable(); // e.g. 10.00
            $table->decimal('max_score', 5, 2)->nullable(); // e.g. 12.99
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
