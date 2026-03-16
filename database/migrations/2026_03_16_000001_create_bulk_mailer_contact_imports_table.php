<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_contact_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bulk_mailer_contact_list_id')
                ->constrained('bulk_mailer_contact_lists')
                ->cascadeOnDelete();

            $table->string('source_type', 20);
            $table->string('source_name')->nullable();
            $table->string('stored_file_path')->nullable();

            $table->string('status', 30)->default('queued');

            $table->unsignedInteger('total_read')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('valid_count')->default(0);
            $table->unsignedInteger('invalid_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('inserted_count')->default(0);

            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at'], 'bm_contact_imports_status_created_idx');
            $table->index(['bulk_mailer_contact_list_id', 'created_at'], 'bm_contact_imports_list_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_contact_imports');
    }
};