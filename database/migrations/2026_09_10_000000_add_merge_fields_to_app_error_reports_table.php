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
            $table->unsignedInteger('occurrence_count')->default(1)->after('checked_by');
            $table->timestamp('first_occurred_at')->nullable()->after('occurrence_count');
            $table->timestamp('last_occurred_at')->nullable()->after('first_occurred_at');
            $table->timestamp('merged_at')->nullable()->after('last_occurred_at')->index();
            $table->foreignId('merged_by')->nullable()->after('merged_at')->constrained('users')->nullOnDelete();
            $table->foreignId('merged_into_id')->nullable()->after('merged_by')->index();
            $table->foreign('merged_into_id')->references('id')->on('app_error_reports')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_error_reports', function (Blueprint $table) {
            $table->dropForeign(['merged_by']);
            $table->dropForeign(['merged_into_id']);
            $table->dropIndex(['merged_at']);
            $table->dropIndex(['merged_into_id']);
            $table->dropColumn([
                'occurrence_count',
                'first_occurred_at',
                'last_occurred_at',
                'merged_at',
                'merged_by',
                'merged_into_id',
            ]);
        });
    }
};
