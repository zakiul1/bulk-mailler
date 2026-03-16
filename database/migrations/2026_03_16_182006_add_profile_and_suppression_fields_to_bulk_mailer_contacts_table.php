<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailer_contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('bulk_mailer_contacts', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'status')) {
                $table->string('status')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'bounced_at')) {
                $table->timestamp('bounced_at')->nullable()->after('unsubscribed_at');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'suppression_reason')) {
                $table->text('suppression_reason')->nullable()->after('bounced_at');
            }

            if (! Schema::hasColumn('bulk_mailer_contacts', 'notes')) {
                $table->text('notes')->nullable()->after('suppression_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_contacts', function (Blueprint $table) {
            $columns = [
                'first_name',
                'last_name',
                'status',
                'unsubscribed_at',
                'bounced_at',
                'suppression_reason',
                'notes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('bulk_mailer_contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};