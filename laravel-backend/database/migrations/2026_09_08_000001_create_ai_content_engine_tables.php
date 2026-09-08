<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40)->default('gemini');
            $table->text('api_key');
            $table->string('model', 120)->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('daily_request_limit')->default(100);
            $table->unsignedBigInteger('daily_token_limit')->default(1000000);
            $table->unsignedInteger('max_items_per_request')->default(20);
            $table->decimal('monthly_budget', 10, 2)->nullable();
            $table->boolean('auto_publish_news')->default(false);
            $table->boolean('auto_publish_jobs')->default(false);
            $table->timestamps();
        });

        Schema::create('ai_contents', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 20); // news|job
            $table->unsignedBigInteger('source_id');
            $table->string('status', 20)->default('pending');
            $table->json('source_snapshot')->nullable();
            $table->json('generated_content')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['source_type', 'source_id']);
            $table->index(['status', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_contents');
        Schema::dropIfExists('ai_provider_settings');
    }
};
