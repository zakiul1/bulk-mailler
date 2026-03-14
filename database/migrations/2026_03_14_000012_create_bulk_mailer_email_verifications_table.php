<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_email_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_mailer_contact_id');
            $table->string('email');
            $table->string('status', 30)->default('pending');
            $table->text('reason')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->foreign('bulk_mailer_contact_id', 'bm_ev_contact_fk')
                ->references('id')
                ->on('bulk_mailer_contacts')
                ->cascadeOnDelete();

            $table->unique('bulk_mailer_contact_id', 'bm_ev_contact_unique');
            $table->index('status', 'bm_ev_status_idx');
            $table->index('email', 'bm_ev_email_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_email_verifications');
    }
};