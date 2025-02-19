<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan role ke user berdasarkan username';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->ask('Masukkan username');
        $roleName = $this->ask('Masukkan nama role');

        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("User dengan username '{$username}' tidak ditemukan!");
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' tidak ditemukan!");
            return;
        }

        $user->assignRole($role);
        $this->info("Role '{$roleName}' berhasil ditambahkan ke user '{$username}'");
    }
}
