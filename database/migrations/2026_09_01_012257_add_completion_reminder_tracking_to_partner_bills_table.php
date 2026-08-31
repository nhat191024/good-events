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
            $table->timestamp('completion_reminder_started_at')->nullable()->after('status');
            $table->index(
                ['partner_id', 'status', 'completion_reminder_started_at'],
                'partner_bills_completion_workflow_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_bills', function (Blueprint $table) {
            $table->dropIndex('partner_bills_completion_workflow_index');
            $table->dropColumn('completion_reminder_started_at');
        });
    }
};
