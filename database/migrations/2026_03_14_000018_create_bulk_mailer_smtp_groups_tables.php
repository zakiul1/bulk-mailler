<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_smtp_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bulk_mailer_smtp_group_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_mailer_smtp_group_id');
            $table->unsignedBigInteger('bulk_mailer_smtp_account_id');
            $table->timestamps();

            $table->foreign('bulk_mailer_smtp_group_id', 'bm_smtp_group_item_group_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_groups')
                ->cascadeOnDelete();

            $table->foreign('bulk_mailer_smtp_account_id', 'bm_smtp_group_item_account_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_accounts')
                ->cascadeOnDelete();

            $table->unique(
                ['bulk_mailer_smtp_group_id', 'bulk_mailer_smtp_account_id'],
                'bm_smtp_group_item_unique'
            );
        });

        Schema::table('bulk_mailer_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('bulk_mailer_smtp_group_id')->nullable()->after('bulk_mailer_segment_id');

            $table->foreign('bulk_mailer_smtp_group_id', 'bm_campaign_smtp_group_fk')
                ->references('id')
                ->on('bulk_mailer_smtp_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_campaigns', function (Blueprint $table) {
            $table->dropForeign('bm_campaign_smtp_group_fk');
            $table->dropColumn('bulk_mailer_smtp_group_id');
        });

        Schema::dropIfExists('bulk_mailer_smtp_group_items');
        Schema::dropIfExists('bulk_mailer_smtp_groups');
    }
};