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
        Schema::table('participants', function (Blueprint $table) {
            $table->string('membership_context', 20)
                ->nullable()
                ->after('last_read');
            $table->index(
                ['user_id', 'deleted_at', 'membership_context', 'thread_id'],
                'participants_membership_lookup_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_membership_lookup_index');
            $table->dropColumn('membership_context');
        });
    }
};
