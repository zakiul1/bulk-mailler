<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_campaign_recipients', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_campaign_id');
            $table->unsignedBigInteger('bulk_mailer_contact_id');
            $table->unsignedBigInteger('bulk_mailer_smtp_account_id')->nullable();

            $table->string('email');
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('bulk_mailer_campaign_id', 'bm_cr_campaign_fk')
                ->references('id')
                ->on('bulk_mailer_campaigns')
                ->cascadeOnDelete();

            $table->foreign('bulk_mailer_contact_id', 'bm_cr_contact_fk')
                ->references('id')
                ->on('bulk_mailer_contacts')
                ->cascadeOnDelete();

            $table->foreign('bulk_mailer_smtp_account_id', 'bm_cr_smtp_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_accounts')
                ->nullOnDelete();

            $table->unique(
                ['bulk_mailer_campaign_id', 'bulk_mailer_contact_id'],
                'bm_cr_campaign_contact_unique'
            );

            $table->index('status', 'bm_cr_status_idx');
            $table->index('email', 'bm_cr_email_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_campaign_recipients');
    }
};