<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('workflow_status', 20)->default('published')->after('section')->index();
            $table->timestamp('scheduled_at')->nullable()->after('published_at')->index();
            $table->unsignedBigInteger('views_count')->default(0)->after('result_date');
            $table->unsignedBigInteger('apply_clicks')->default(0)->after('views_count');
            $table->unsignedBigInteger('notification_count')->default(0)->after('apply_clicks');
            $table->timestamp('last_notification_at')->nullable()->after('notification_count');
        });

        DB::table('jobs')->whereNull('workflow_status')->update(['workflow_status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['workflow_status','scheduled_at','views_count','apply_clicks','notification_count','last_notification_at']);
        });
    }
};
