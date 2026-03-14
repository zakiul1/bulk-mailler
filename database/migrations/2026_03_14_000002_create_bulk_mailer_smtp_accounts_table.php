<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_mailer_smtp_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('encryption', 20)->nullable();
            $table->string('username');
            $table->text('password');
            $table->string('from_name');
            $table->string('from_email');
            $table->string('reply_to_email')->nullable();
            $table->unsignedInteger('daily_limit')->default(500);
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('health_status', 30)->default('unknown');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index('health_status');
            $table->index('from_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_mailer_smtp_accounts');
    }
};