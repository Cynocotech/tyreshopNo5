<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password', // User model casts to hashed
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            SiteSettingsSeeder::class,
            FaqSeeder::class,
            AreaSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
