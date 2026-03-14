<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_delivery_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_campaign_id')->nullable();
            $table->unsignedBigInteger('bulk_mailer_contact_id')->nullable();
            $table->unsignedBigInteger('bulk_mailer_campaign_recipient_id')->nullable();

            $table->string('email');
            $table->string('event_type', 50);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('event_at')->nullable();
            $table->timestamps();

            $table->foreign('bulk_mailer_campaign_id', 'bm_de_campaign_fk')
                ->references('id')
                ->on('bulk_mailer_campaigns')
                ->nullOnDelete();

            $table->foreign('bulk_mailer_contact_id', 'bm_de_contact_fk')
                ->references('id')
                ->on('bulk_mailer_contacts')
                ->nullOnDelete();

            $table->foreign('bulk_mailer_campaign_recipient_id', 'bm_de_recipient_fk')
                ->references('id')
                ->on('bulk_mailer_campaign_recipients')
                ->nullOnDelete();

            $table->index('email', 'bm_de_email_idx');
            $table->index('event_type', 'bm_de_event_type_idx');
            $table->index('event_at', 'bm_de_event_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_delivery_events');
    }
};