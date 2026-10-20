<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneOldPartnerBillChats extends Command
{
    protected $signature = 'partner-bills:prune-old-chat-media
        {--days=14 : Delete ended bill chats inactive for this many days}
        {--chunk=100 : Number of threads to process per chunk}
        {--dry-run : Show what would be deleted without deleting anything}';

    protected $description = 'Delete inactive chats for ended partner bills while preserving bills and their own media.';

    public function handle(): int
    {
        return $this->call('chat:prune-old', [
            '--days' => $this->option('days'),
            '--chunk' => $this->option('chunk'),
            '--dry-run' => (bool) $this->option('dry-run'),
            '--only-bills' => true,
        ]);
    }
}
