<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('labellisation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insérer les valeurs par défaut
        DB::table('labellisation_settings')->insert([
            [
                'key' => 'note_scale',
                'value' => '20',
                'label' => 'Échelle de notation',
                'description' => 'Échelle maximale des notes (ex: 5, 10, 20)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labellisation_settings');
    }
};
