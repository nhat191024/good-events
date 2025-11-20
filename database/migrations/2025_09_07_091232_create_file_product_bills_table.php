<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enum\FileProductBillStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_product_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_product_id')->constrained('file_products')->restrictOnDelete();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->double('total');
            $table->double('tax')->nullable();
            $table->double('final_total')->nullable();
            $table->integer('tax_number')->nullable();
            $table->string('company_name')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default(FileProductBillStatus::PENDING);
            $table->string('payment_method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_product_bills');
    }
};
