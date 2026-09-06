<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. App Sections Table
        if (!Schema::hasTable('app_sections')) {
            Schema::create('app_sections', function (Blueprint $table) {
                $table->id();
                $table->string('section_key')->unique(); // jobs, news, static_gk
                $table->string('name');
                $table->string('hindi_name')->nullable();
                $table->text('description')->nullable();
                $table->string('icon')->default('folder');
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Add section_id to categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'section_id')) {
                $table->string('section_id')->default('jobs')->after('id');
            }
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('display_order');
            }
        });

        // 3. Add section & post_type to jobs
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'section')) {
                $table->string('section')->default('jobs')->after('category');
            }
            if (!Schema::hasColumn('jobs', 'post_type')) {
                $table->string('post_type')->default('job')->after('section');
            }
            if (!Schema::hasColumn('jobs', 'salary')) {
                $table->string('salary')->nullable()->after('vacancies');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_sections');
    }
};
