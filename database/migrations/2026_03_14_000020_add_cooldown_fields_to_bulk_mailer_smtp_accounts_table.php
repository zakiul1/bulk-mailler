<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_smtp_accounts', function (Blueprint $table) {
            $table->unsignedInteger('failure_count')->default(0)->after('health_status');
            $table->unsignedInteger('consecutive_failures')->default(0)->after('failure_count');
            $table->timestamp('last_failed_at')->nullable()->after('consecutive_failures');
            $table->timestamp('cooldown_until')->nullable()->after('last_failed_at');
            $table->timestamp('last_success_at')->nullable()->after('cooldown_until');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_smtp_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'failure_count',
                'consecutive_failures',
                'last_failed_at',
                'cooldown_until',
                'last_success_at',
            ]);
        });
    }
};