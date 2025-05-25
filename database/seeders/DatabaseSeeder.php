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
        $this->call([
            UserTableSeeder::class,
            WasteTypeSeeder::class,
            WasteItemSeeder::class,
            RecyclingCenterSeeder::class,
            BadgeSeeder::class,
            RewardTableSeeder::class,

            // Point Transactions and Recycling History
            RecyclingHistoryTableSeeder::class,

            RewardRedemptionTableSeeder::class,
        ]);
    }
}
