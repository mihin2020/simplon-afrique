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
        Schema::create('promotion_formateur', function (Blueprint $table) {
            $table->uuid('promotion_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary(['promotion_id', 'user_id']);

            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_formateur');
    }
};
