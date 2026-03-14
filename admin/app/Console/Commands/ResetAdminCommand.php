<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetAdminCommand extends Command
{
    protected $signature = 'no5:reset-admin 
                            {--email=admin@example.com : Admin email}
                            {--password=password : New password}';

    protected $description = 'Create or reset the admin user (fixes login after database reset)';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => $password, // User model casts to hashed
                'email_verified_at' => now(),
            ]
        );

        $this->info('Admin user ready.');
        $this->line("  Email:    {$email}");
        $this->line('  Password: ' . $password);
        $this->newLine();
        $this->comment('Log in at /login');

        return 0;
    }
}
