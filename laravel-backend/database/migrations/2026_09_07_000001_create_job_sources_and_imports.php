<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('fetch_url');
            $table->string('source_type')->default('html');
            $table->unsignedInteger('frequency_minutes')->default(60);
            $table->string('publish_mode')->default('approval');
            $table->string('default_category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('last_items_fetched')->default(0);
            $table->unsignedInteger('last_items_imported')->default(0);
            $table->unsignedInteger('last_items_skipped')->default(0);
            $table->timestamps();
        });

        Schema::create('job_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_source_id')->constrained('job_sources')->cascadeOnDelete();
            $table->string('external_key')->nullable();
            $table->text('external_url');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('category')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['job_source_id', 'external_url']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_imports');
        Schema::dropIfExists('job_sources');
    }
};
