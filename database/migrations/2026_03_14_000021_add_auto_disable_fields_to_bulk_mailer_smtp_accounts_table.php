<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_smtp_accounts', function (Blueprint $table) {
            $table->timestamp('auto_disabled_at')->nullable()->after('last_success_at');
            $table->text('auto_disabled_reason')->nullable()->after('auto_disabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_smtp_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'auto_disabled_at',
                'auto_disabled_reason',
            ]);
        });
    }
};