<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Fauzan Mazlam',
            'email' => 'fauzanmazlam88@gmail.com',
        ]);

        $this->call([
            WasteTypeSeeder::class,
            WasteItemSeeder::class,
            RecyclingCenterSeeder::class,
            CleanupEventSeeder::class,
            BadgeSeeder::class,
        ]);
    }
}
