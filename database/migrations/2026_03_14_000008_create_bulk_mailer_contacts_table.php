<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_mailer_contact_list_id')
                ->constrained('bulk_mailer_contact_lists')
                ->cascadeOnDelete();

            $table->string('email');
            $table->timestamps();

            $table->unique(
                ['bulk_mailer_contact_list_id', 'email'],
                'bm_contacts_list_email_unique'
            );

            $table->index('email', 'bm_contacts_email_idx');
            $table->index(
                ['bulk_mailer_contact_list_id', 'created_at'],
                'bm_contacts_list_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_contacts');
    }
};