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
        Schema::create('partner_bills', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('address');
            $table->string('phone');
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->double('total')->nullable();
            $table->double('final_total')->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events')->restrictOnDelete();
            $table->string('custom_event')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('partner_categories')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->string('status');
            $table->unsignedInteger('thread_id')->nullable();
            $table->foreign('thread_id')->references('id')->on('threads')->restrictOnDelete();
            $table->foreign('voucher_id')->references('id')->on('vouchers')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
