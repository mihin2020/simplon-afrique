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
        Schema::table('promotion_notes', function (Blueprint $table) {
            $table->text('difficulties')->nullable()->after('content');
            $table->text('recommendations')->nullable()->after('difficulties');
            $table->text('other')->nullable()->after('recommendations');
            $table->text('content')->nullable()->change();
            $table->string('note_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_notes', function (Blueprint $table) {
            $table->dropColumn(['difficulties', 'recommendations', 'other']);
            $table->text('content')->nullable(false)->change();
            $table->string('note_type')->nullable(false)->change();
        });
    }
};
