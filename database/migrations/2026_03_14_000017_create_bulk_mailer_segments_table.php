<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('bulk_mailer_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('bulk_mailer_segment_id')->nullable()->after('bulk_mailer_template_id');
            $table->string('subject_a')->nullable()->after('subject');
            $table->string('subject_b')->nullable()->after('subject_a');
            $table->boolean('ab_testing_enabled')->default(false)->after('subject_b');

            $table->foreign('bulk_mailer_segment_id', 'bm_campaign_segment_fk')
                ->references('id')
                ->on('bulk_mailer_segments')
                ->nullOnDelete();
        });

        Schema::table('bulk_mailer_campaign_recipients', function (Blueprint $table) {
            $table->string('subject_variant', 10)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailer_campaign_recipients', function (Blueprint $table) {
            $table->dropColumn('subject_variant');
        });

        Schema::table('bulk_mailer_campaigns', function (Blueprint $table) {
            $table->dropForeign('bm_campaign_segment_fk');
            $table->dropColumn([
                'bulk_mailer_segment_id',
                'subject_a',
                'subject_b',
                'ab_testing_enabled',
            ]);
        });

        Schema::dropIfExists('bulk_mailer_segments');
    }
};