<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class VibeCreateAdminCommand extends Command
{
    protected $signature = 'vibe:create-admin';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $name = $this->ask('Name', 'Admin');
        $email = $this->ask('Email', 'admin@ovc.vn');
        $password = $this->secret('Password');

        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists.');
            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('Admin user created successfully.');
    }
}
