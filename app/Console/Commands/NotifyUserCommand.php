<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->ask('Enter username');
        $title = $this->ask('Enter the notification title');
        $message = $this->ask('Enter the message');

        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $user->notify(new Notification($title, $message));
    }
}
