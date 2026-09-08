<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('published_web')->default(false)->after('status');
            $table->boolean('published_mobile')->default(false)->after('published_web');
            $table->index(['status', 'published_web']);
            $table->index(['status', 'published_mobile']);
        });

        DB::table('news')->where('status', 'published')->update([
            'published_web' => true,
            'published_mobile' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_web']);
            $table->dropIndex(['status', 'published_mobile']);
            $table->dropColumn(['published_web', 'published_mobile']);
        });
    }
};
