<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_delivery_events', function (Blueprint $table) {
            $table->string('provider', 50)->nullable()->after('event_type');
            $table->string('provider_event_id', 191)->nullable()->after('provider');
            $table->index(['provider', 'provider_event_id'], 'bm_de_provider_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_delivery_events', function (Blueprint $table) {
            $table->dropIndex('bm_de_provider_event_idx');
            $table->dropColumn(['provider', 'provider_event_id']);
        });
    }
};