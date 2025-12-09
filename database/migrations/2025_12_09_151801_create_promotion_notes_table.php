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
        Schema::create('promotion_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('promotion_id')->nullable();
            $table->uuid('admin_id');
            $table->uuid('created_by');
            $table->string('title');
            $table->text('content');
            $table->string('note_type');
            $table->timestamps();

            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->nullOnDelete();

            $table->foreign('admin_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('created_by')
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
        Schema::dropIfExists('promotion_notes');
    }
};
