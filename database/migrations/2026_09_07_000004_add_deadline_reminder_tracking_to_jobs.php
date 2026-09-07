<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->timestamp('closing_reminder_sent_at')->nullable()->after('result_date');
            $table->index('closing_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['closing_reminder_sent_at']);
            $table->dropColumn('closing_reminder_sent_at');
        });
    }
};
