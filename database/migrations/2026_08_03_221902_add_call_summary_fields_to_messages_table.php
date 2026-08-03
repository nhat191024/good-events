<?php

use Cmgmyr\Messenger\Models\Models;
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
        Schema::table(Models::table('messages'), function (Blueprint $table) {
            $table->foreignId('call_id')
                ->nullable()
                ->unique()
                ->after('client_message_id')
                ->constrained('calls')
                ->nullOnDelete();
            $table->unsignedInteger('call_duration_seconds')
                ->nullable()
                ->after('call_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Models::table('messages'), function (Blueprint $table) {
            $table->dropConstrainedForeignId('call_id');
            $table->dropColumn('call_duration_seconds');
        });
    }
};
