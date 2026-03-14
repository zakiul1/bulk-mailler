<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_contact_list_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_contact_id');
            $table->unsignedBigInteger('bulk_mailer_contact_list_id');

            $table->timestamps();

            $table->foreign('bulk_mailer_contact_id', 'bm_cli_contact_fk')
                ->references('id')
                ->on('bulk_mailer_contacts')
                ->cascadeOnDelete();

            $table->foreign('bulk_mailer_contact_list_id', 'bm_cli_list_fk')
                ->references('id')
                ->on('bulk_mailer_contact_lists')
                ->cascadeOnDelete();

            $table->unique(
                ['bulk_mailer_contact_id', 'bulk_mailer_contact_list_id'],
                'bm_cli_contact_list_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_contact_list_items');
    }
};