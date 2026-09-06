<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jobs', 'published_by')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->string('published_by', 255)->nullable()->after('department');
            });
        }

        if (!Schema::hasColumn('job_imports', 'published_by')) {
            Schema::table('job_imports', function (Blueprint $table) {
                $table->string('published_by', 255)->nullable()->after('department');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jobs', 'published_by')) Schema::table('jobs', fn (Blueprint $table) => $table->dropColumn('published_by'));
        if (Schema::hasColumn('job_imports', 'published_by')) Schema::table('job_imports', fn (Blueprint $table) => $table->dropColumn('published_by'));
    }
};
