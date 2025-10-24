<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder user admin
        User::create([
            'full_name' => 'Admin KAI',
            'email' => 'adminzz@gmail.com',
            'password' => \Hash::make('adminkai'),
        ]);

        $this->call([
            StationSeeder::class,
            VisitTypeSeeder::class,
        ]);

        $this->call([
            OptionListSeeder::class,
     ]);
    }
}
