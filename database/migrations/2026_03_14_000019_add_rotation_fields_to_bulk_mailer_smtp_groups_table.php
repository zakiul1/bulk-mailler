<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_smtp_groups', function (Blueprint $table) {
            $table->string('rotation_mode', 30)
                ->default('priority')
                ->after('is_active');

            $table->unsignedBigInteger('last_used_smtp_account_id')
                ->nullable()
                ->after('rotation_mode');

            $table->foreign('last_used_smtp_account_id', 'bm_smtp_groups_last_used_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_smtp_groups', function (Blueprint $table) {
            $table->dropForeign('bm_smtp_groups_last_used_fk');
            $table->dropColumn([
                'rotation_mode',
                'last_used_smtp_account_id',
            ]);
        });
    }
};