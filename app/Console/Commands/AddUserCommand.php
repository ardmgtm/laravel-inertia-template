<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AddUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Enter name');
        $username = $this->ask('Enter username');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password');

        // Check if a user with the same username already exists
        if (User::where('username', $username)->exists()) {
            $this->error('A user with this username already exists!');
            return;
        }

        // Check if a user with the same email already exists
        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists!');
            return;
        }

        // Add user to the database
        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
