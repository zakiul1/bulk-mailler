<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_smtp_daily_usages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_smtp_account_id');

            $table->date('usage_date');
            $table->unsignedInteger('emails_sent')->default(0);
            $table->timestamps();

            $table->foreign('bulk_mailer_smtp_account_id', 'bm_smtp_daily_usage_smtp_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_accounts')
                ->cascadeOnDelete();

            $table->unique(
                ['bulk_mailer_smtp_account_id', 'usage_date'],
                'bm_smtp_daily_usage_unique'
            );

            $table->index('usage_date', 'bm_smtp_daily_usage_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_smtp_daily_usages');
    }
};