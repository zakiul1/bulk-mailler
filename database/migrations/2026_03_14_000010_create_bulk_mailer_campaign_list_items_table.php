<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_campaign_list_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_campaign_id');
            $table->unsignedBigInteger('bulk_mailer_contact_list_id');

            $table->timestamps();

            $table->foreign('bulk_mailer_campaign_id', 'bm_cli_campaign_fk')
                ->references('id')
                ->on('bulk_mailer_campaigns')
                ->cascadeOnDelete();

            $table->foreign('bulk_mailer_contact_list_id', 'bm_cli_list_fk_campaign')
                ->references('id')
                ->on('bulk_mailer_contact_lists')
                ->cascadeOnDelete();

            $table->unique(
                ['bulk_mailer_campaign_id', 'bulk_mailer_contact_list_id'],
                'bm_campaign_list_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_campaign_list_items');
    }
};