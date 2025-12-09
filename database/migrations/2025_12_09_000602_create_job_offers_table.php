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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->enum('contract_type', ['cdi', 'cdd', 'stage', 'alternance', 'freelance']);
            $table->string('location');
            $table->enum('remote_policy', ['sur_site', 'hybride', 'full_remote'])->default('sur_site');
            $table->text('description');
            $table->string('experience_years');
            $table->string('minimum_education');
            $table->json('required_skills');
            $table->date('application_deadline');
            $table->text('additional_info')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['status', 'application_deadline']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
