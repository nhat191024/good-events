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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();
            $table->unsignedInteger('thread_id');
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->string('channel', 64)->unique();
            $table->string('type', 16);
            $table->string('status', 16)->index();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('threads')->cascadeOnDelete();
            $table->index(['thread_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
