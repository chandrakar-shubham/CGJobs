<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'exam_takeaway')) {
                $table->text('exam_takeaway')->nullable()->after('selection_process');
            }
            if (!Schema::hasColumn('jobs', 'slug')) {
                $table->string('slug')->nullable()->after('custom_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'exam_takeaway')) {
                $table->dropColumn('exam_takeaway');
            }
            if (Schema::hasColumn('jobs', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
