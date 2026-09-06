<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('summary_en')->nullable()->after('summary');
            $table->longText('detailed_content_en')->nullable()->after('detailed_content');
            $table->string('category_en')->nullable()->after('category');
            $table->text('eligibility_en')->nullable()->after('eligibility');
            $table->text('selection_process_en')->nullable()->after('selection_process');
            $table->string('relative_time_en')->nullable()->after('relative_time');
        });

        Schema::table('static_gks', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('category_en')->nullable()->after('category');
            $table->text('question_en')->nullable()->after('question');
            $table->text('answer_en')->nullable()->after('answer');
            $table->json('key_points_en')->nullable()->after('key_points');
            $table->longText('detailed_notes_en')->nullable()->after('detailed_notes');
            $table->string('year_exam_reference_en')->nullable()->after('year_exam_reference');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'summary_en', 'detailed_content_en', 'category_en',
                'eligibility_en', 'selection_process_en', 'relative_time_en',
            ]);
        });

        Schema::table('static_gks', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'category_en', 'question_en', 'answer_en',
                'key_points_en', 'detailed_notes_en', 'year_exam_reference_en',
            ]);
        });
    }
};
