<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE promotions MODIFY admin_id CHAR(36) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE promotions MODIFY admin_id CHAR(36) NOT NULL');
    }
};
