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
        Schema::create('partner_bill_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_category_accessory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('surcharge', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['partner_bill_id', 'partner_category_accessory_id'],
                'bill_accessory_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_bill_accessories');
    }
};
