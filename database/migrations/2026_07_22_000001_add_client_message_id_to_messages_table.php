<?php

use Cmgmyr\Messenger\Models\Models;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Models::table('messages'), function (Blueprint $table): void {
            $table->uuid('client_message_id')->nullable()->after('user_id');
            $table->unique(
                ['thread_id', 'user_id', 'client_message_id'],
                'messages_thread_user_client_message_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table(Models::table('messages'), function (Blueprint $table): void {
            $table->dropUnique('messages_thread_user_client_message_unique');
            $table->dropColumn('client_message_id');
        });
    }
};
