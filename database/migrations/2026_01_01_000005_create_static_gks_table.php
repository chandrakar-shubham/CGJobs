<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_gks', function (Blueprint $table) {
            $table->id();
            $table->string('custom_id')->nullable()->unique();
            $table->string('title');
            $table->string('hindi_title')->nullable();
            $table->string('category')->default('छत्तीसगढ़ का इतिहास');
            $table->string('category_hindi')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->json('key_points')->nullable();
            $table->longText('detailed_notes')->nullable();
            $table->string('year_exam_reference')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_gks');
    }
};
