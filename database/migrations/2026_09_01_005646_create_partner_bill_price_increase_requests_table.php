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
        Schema::create('partner_bill_price_increase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('message_id')->nullable()->unique('price_increase_requests_message_unique');
            $table->foreign('message_id')->references('id')->on('messages')->nullOnDelete();
            $table->unsignedBigInteger('original_total');
            $table->unsignedBigInteger('requested_total');
            $table->text('reason');
            $table->string('status')->index('price_increase_requests_status_index');
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['partner_bill_id', 'created_at'], 'price_increase_requests_bill_created_index');
            $table->index(['partner_bill_id', 'status'], 'price_increase_requests_bill_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_bill_price_increase_requests');
    }
};
