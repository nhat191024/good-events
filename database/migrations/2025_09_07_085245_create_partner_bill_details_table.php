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
        Schema::create('partner_bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_bill_id')->constrained('partner_bills')->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->double('total');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_bill_details');
    }
};
