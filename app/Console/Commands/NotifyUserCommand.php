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
    protected $signature = 'user:notify 
                            {username? : The username to send notification to}
                            {title? : The notification title}
                            {message? : The notification message}
                            {--test : Send test notification to admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // If test flag is used, send test notification to admin
        if ($this->option('test')) {
            $username = 'admin';
            $title = 'Test Notification';
            $message = 'This is a test notification message sent from the command line.';
        } else {
            // Use provided arguments or prompt interactively
            $username = $this->argument('username') ?? $this->ask('Enter username');
            $title = $this->argument('title') ?? $this->ask('Enter the notification title');
            $message = $this->argument('message') ?? $this->ask('Enter the message');
        }

        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error("User '{$username}' not found");

            return Command::FAILURE;
        }

        $user->notify(new Notification($title, $message));

        $this->info("Notification sent successfully to {$username}!");

        return Command::SUCCESS;
    }
}
