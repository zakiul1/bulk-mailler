<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_smtp_account_tag', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bulk_mailer_smtp_account_id')
                ->constrained('bulk_mailer_smtp_accounts')
                ->cascadeOnDelete();

            $table->foreignId('bulk_mailer_tag_id')
                ->constrained('bulk_mailer_tags')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['bulk_mailer_smtp_account_id', 'bulk_mailer_tag_id'],
                'bulk_mailer_smtp_tag_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_smtp_account_tag');
    }
};