<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('custom_id')->nullable()->unique();
            $table->string('title');
            $table->text('summary');
            $table->longText('detailed_content')->nullable();
            $table->string('category')->default('CG Vyapam');
            $table->string('source')->nullable()->default('cgstate.gov.in');
            $table->string('source_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('published_at')->nullable();
            $table->string('relative_time')->nullable()->default('हाल ही में');
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_new')->default(true);
            $table->string('vacancies')->nullable();
            $table->text('eligibility')->nullable();
            $table->string('age_limit')->nullable();
            $table->text('selection_process')->nullable();
            $table->string('official_notification_url')->nullable();
            $table->string('apply_url')->nullable();
            
            // Important Dates
            $table->string('application_start')->nullable()->default('जारी');
            $table->string('last_date')->nullable()->default('शीघ्र');
            $table->string('exam_date')->nullable();
            $table->string('admit_card_date')->nullable();
            $table->string('result_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
