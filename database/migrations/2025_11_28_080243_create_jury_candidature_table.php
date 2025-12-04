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
        Schema::create('jury_candidature', function (Blueprint $table) {
            $table->uuid('jury_id');
            $table->uuid('candidature_id');
            $table->timestamps();

            $table->primary(['jury_id', 'candidature_id']);

            $table->foreign('jury_id')
                ->references('id')
                ->on('juries')
                ->cascadeOnDelete();

            $table->foreign('candidature_id')
                ->references('id')
                ->on('candidatures')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jury_candidature');
    }
};
