<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_imports', function (Blueprint $table) {
            $table->json('image_urls')->nullable()->after('image_url');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->json('image_urls')->nullable()->after('image_url');
        });

        Schema::table('job_sources', function (Blueprint $table) {
            $table->boolean('notify_on_publish')->default(false)->after('publish_mode');
        });
    }

    public function down(): void
    {
        Schema::table('job_sources', function (Blueprint $table) {
            $table->dropColumn('notify_on_publish');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('image_urls');
        });

        Schema::table('job_imports', function (Blueprint $table) {
            $table->dropColumn('image_urls');
        });
    }
};
