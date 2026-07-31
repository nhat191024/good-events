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
        Schema::table('app_error_reports', function (Blueprint $table) {
            $table->timestamp('checked_at')->nullable()->after('occurred_at')->index();
            $table->foreignId('checked_by')
                ->nullable()
                ->after('checked_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_error_reports', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropColumn(['checked_at', 'checked_by']);
        });
    }
};
