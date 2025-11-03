<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AddMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-money {user_id : The ID of the user} {amount : The amount of money to add}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add money to a user wallet';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $amount = $this->argument('amount');

        $this->info("User ID: {$userId}");
        $this->info("Amount: {$amount}");

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return self::FAILURE;
        }

        $user->deposit($amount, ['reason' => 'Admin added money via command']);
        $this->info("Successfully added {$amount} to user ID {$userId}'s wallet.");

        return self::SUCCESS;
    }
}
