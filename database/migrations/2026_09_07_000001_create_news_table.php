<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('summary')->nullable();
            $table->text('summary_en')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('source')->nullable();
            $table->text('source_url')->nullable();
            $table->text('original_url')->nullable();
            $table->text('image_url')->nullable();
            $table->string('category')->nullable();
            $table->string('category_en')->nullable();
            $table->json('tags')->nullable();
            $table->string('language', 10)->default('hi');
            $table->dateTime('published_at')->nullable();
            $table->unsignedTinyInteger('exam_relevance')->default(0);
            $table->boolean('featured')->default(false);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['category', 'published_at']);
            $table->index('exam_relevance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
