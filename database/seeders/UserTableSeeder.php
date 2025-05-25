<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon; // Import Carbon

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ID: 1 [ADMIN]
        User::factory()->create([
            'name' => 'Fauzan Mazlam',
            'email' => 'fauzanmazlam88@gmail.com',
            'account_type' => 'admin',
            'phone' => '081234567890',
            'location' => 'Terengganu, Malaysia',
            'bio' => 'Admin of SmartWaste',
            'created_at' => Carbon::create(2024, 5, 1, 10, 0, 0) // Example fixed date for admin
        ]);

        // ID: 2-17 [RECYCLING CENTER] - Assuming these can have recent created_at or specific dates
        // For simplicity, let's give them created_at within May 2024
        for ($i = 0; $i < 16; $i++) {
            User::factory()->create([
                'account_type' => 'user', // Should this be 'recycling_center'? Based on comment
                'created_at' => Carbon::create(2024, 5, rand(1, 31), rand(0, 23), rand(0, 59), rand(0, 59)),
            ]);
        }


        // ID: 18 [USER] - Example Specific User
        User::factory()->create([
            'name' => 'Muhammad Muzzafar Bin Naim',
            'email' => 'muzzafarnaim@gmail.com',
            'phone' => '0123456789',
            'location' => 'Kuala Lumpur, Malaysia',
            'bio' => 'User of SmartWaste',
            'account_type' => 'user',
            'created_at' => Carbon::create(2024, 5, 15, 12, 0, 0) // Example fixed date
        ]);

        // ID: 19 [USER] - Example Specific User
        User::factory()->create([
            'name' => 'Nurul Aisyah Binti Ahmad',
            'email' => 'aisyahahmad@gmail.com',
            'phone' => '01987654321',
            'location' => 'Selangor, Malaysia',
            'bio' => 'User of SmartWaste',
            'account_type' => 'user',
            'created_at' => Carbon::create(2024, 5, 20, 14, 30, 0) // Example fixed date
        ]);

        // ID: 20 [USER] - Example Specific User
        User::factory()->create([
            'name' => 'Ahmad Faizal Bin Ismail',
            'email' => 'faizalismail@gmail.com',
            'phone' => '0198765432',
            'location' => 'Penang, Malaysia',
            'bio' => 'User of SmartWaste',
            'account_type' => 'user',
            'created_at' => Carbon::create(2024, 5, 25, 16, 0, 0) // Example fixed date
        ]);

        // --- Dynamically created users from June 2024 to May 2025 ---
        $startDate = Carbon::create(2024, 6, 1); // Start from June 2024
        $endDate = Carbon::create(2025, 5, 1);   // End in May 2025

        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $usersThisMonth = rand(2, 3); // Create 2 or 3 users for the current month

            for ($i = 0; $i < $usersThisMonth; $i++) {
                // Generate a random day within the current month
                $daysInMonth = $currentDate->daysInMonth;
                $randomDay = rand(1, $daysInMonth);

                // Create a Carbon instance for a random time on that day in the current month and year
                $randomCreatedAt = Carbon::create(
                    $currentDate->year,
                    $currentDate->month,
                    $randomDay,
                    rand(0, 23),   // Random hour
                    rand(0, 59),   // Random minute
                    rand(0, 59)    // Random second
                );

                User::factory()->create([
                    'account_type' => 'user', // Assuming these are regular users
                    'created_at' => $randomCreatedAt,
                    'updated_at' => $randomCreatedAt, // Often good to set updated_at to created_at initially
                ]);
            }
            $currentDate->addMonth(); // Move to the next month
        }
    }
}