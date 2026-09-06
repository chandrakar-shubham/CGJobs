<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('custom_id')->nullable()->unique();
            $table->string('category')->default('सूचना');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('time')->nullable()->default('हाल ही में');
            $table->string('type')->default('BREAKING'); // BREAKING, RECRUITMENT, EXAM_DATE, ADMIT_CARD, RESULT, DEADLINE
            $table->boolean('is_read')->default(false);
            $table->string('article_id')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_broadcasted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
