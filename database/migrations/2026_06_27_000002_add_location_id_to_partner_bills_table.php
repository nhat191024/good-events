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
        Schema::table('partner_bills', function (Blueprint $table) {
            $table->foreignId('location_id')
                ->nullable()
                ->after('address')
                ->constrained('locations')
                ->nullOnDelete();

            $table->index(['category_id', 'location_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_bills', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'location_id', 'status']);
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
