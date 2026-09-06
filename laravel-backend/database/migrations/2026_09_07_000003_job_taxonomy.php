<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('job_category')->nullable()->after('category');
            $table->string('department')->nullable()->after('job_category');
            $table->string('poster_path')->nullable()->after('image_url');
            $table->index(['section', 'job_category']);
            $table->index(['section', 'department']);
        });

        Schema::table('job_imports', function (Blueprint $table) {
            $table->string('job_category')->nullable()->after('category');
            $table->string('department')->nullable()->after('job_category');
        });

        DB::table('jobs')->where(function ($q) {
            $q->where('section', 'jobs')->orWhereNull('section');
        })->update(['job_category' => 'CGSSB']);

        DB::table('jobs')->whereIn('category', ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'])
            ->update(['job_category' => DB::raw('category'), 'department' => null]);

        DB::table('jobs')->whereNull('department')->whereNotNull('category')
            ->whereNotIn('category', ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'])
            ->update(['department' => DB::raw('category')]);
    }

    public function down(): void
    {
        Schema::table('job_imports', function (Blueprint $table) {
            $table->dropColumn(['job_category', 'department']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['section', 'job_category']);
            $table->dropIndex(['section', 'department']);
            $table->dropColumn(['job_category', 'department', 'poster_path']);
        });
    }
};
