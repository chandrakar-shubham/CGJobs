<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('translation_status')->default('pending')->after('relative_time_en');
            $table->text('translation_error')->nullable()->after('translation_status');
            $table->timestamp('translated_at')->nullable()->after('translation_error');
        });

        Schema::table('static_gks', function (Blueprint $table) {
            $table->string('translation_status')->default('pending')->after('year_exam_reference_en');
            $table->text('translation_error')->nullable()->after('translation_status');
            $table->timestamp('translated_at')->nullable()->after('translation_error');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['translation_status','translation_error','translated_at']);
        });
        Schema::table('static_gks', function (Blueprint $table) {
            $table->dropColumn(['translation_status','translation_error','translated_at']);
        });
    }
};
