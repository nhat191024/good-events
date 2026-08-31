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
        Schema::table('file_product_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique()->after('payment_method');
            $table->string('payos_payment_link_id')->nullable()->index()->after('payos_order_code');
            $table->json('payos_data')->nullable()->after('payos_payment_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_product_bills', function (Blueprint $table) {
            $table->dropUnique(['payos_order_code']);
            $table->dropIndex(['payos_payment_link_id']);
            $table->dropColumn([
                'payos_order_code',
                'payos_payment_link_id',
                'payos_data',
            ]);
        });
    }
};
