<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_contact_delete_jobs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bulk_mailer_contact_list_id')->nullable();

            $table->string('status', 30)->default('queued');
            $table->string('selection_type', 30)->default('selected');
            $table->json('filters')->nullable();

            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('deleted_count')->default(0);

            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('bulk_mailer_contact_list_id', 'bm_cdel_jobs_list_fk')
                ->references('id')
                ->on('bulk_mailer_contact_lists')
                ->nullOnDelete();

            $table->index(['status', 'created_at'], 'bm_contact_delete_jobs_status_created_idx');
            $table->index(['bulk_mailer_contact_list_id', 'created_at'], 'bm_contact_delete_jobs_list_created_idx');
            $table->index(['created_by', 'created_at'], 'bm_contact_delete_jobs_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_contact_delete_jobs');
    }
};