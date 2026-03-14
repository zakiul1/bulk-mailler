<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_contacts', function (Blueprint $table) {
            $table->timestamp('unsubscribed_at')->nullable()->after('last_verified_at');
            $table->timestamp('bounced_at')->nullable()->after('unsubscribed_at');

            $table->index('unsubscribed_at', 'bm_contacts_unsubscribed_at_idx');
            $table->index('bounced_at', 'bm_contacts_bounced_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_contacts', function (Blueprint $table) {
            $table->dropIndex('bm_contacts_unsubscribed_at_idx');
            $table->dropIndex('bm_contacts_bounced_at_idx');
            $table->dropColumn(['unsubscribed_at', 'bounced_at']);
        });
    }
};