<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_error_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32)->index();
            $table->string('severity', 16)->index();
            $table->string('custom_type')->nullable();
            $table->string('error_code')->nullable()->index();
            $table->text('message');
            $table->string('source')->nullable();
            $table->text('stack_trace')->nullable();
            $table->json('context')->nullable();
            $table->string('api_method', 10)->nullable();
            $table->text('api_url')->nullable();
            $table->unsignedSmallInteger('api_status_code')->nullable()->index();
            $table->json('api_request')->nullable();
            $table->json('api_response')->nullable();
            $table->string('app_version')->nullable()->index();
            $table->string('platform', 50)->nullable()->index();
            $table->string('os_version')->nullable();
            $table->string('device_model')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['type', 'severity', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_error_reports');
    }
};
