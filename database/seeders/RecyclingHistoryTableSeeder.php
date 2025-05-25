<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RecyclingHistory;
use App\Models\PointsTransaction;
use App\Models\WasteItem;
use App\Models\RecyclingCenter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecyclingHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define users who will have recycling history
        $userIds = [18, 19, 20];

        // Get available waste items and recycling centers
        $wasteItems = WasteItem::all();
        $recyclingCenters = RecyclingCenter::all();

        if ($wasteItems->isEmpty() || $recyclingCenters->isEmpty()) {
            $this->command->error('Please make sure waste_items and recycling_centers tables have data before running this seeder.');
            return;
        }

        // Define some common waste items with their typical points
        $wasteData = [
            ['name' => 'Plastic Bottles', 'points' => 10, 'units' => ['pieces', 'kg']],
            ['name' => 'Aluminum Cans', 'points' => 15, 'units' => ['pieces', 'kg']],
            ['name' => 'Paper', 'points' => 5, 'units' => ['kg', 'sheets']],
            ['name' => 'Glass Bottles', 'points' => 12, 'units' => ['pieces', 'kg']],
            ['name' => 'Cardboard', 'points' => 8, 'units' => ['kg', 'pieces']],
            ['name' => 'Electronic Waste', 'points' => 25, 'units' => ['pieces', 'kg']],
            ['name' => 'Metal Scraps', 'points' => 20, 'units' => ['kg']],
            ['name' => 'Magazines', 'points' => 6, 'units' => ['pieces', 'kg']],
        ];

        foreach ($userIds as $userId) {
            // Generate 5-6 recycling records per user
            $recordCount = rand(5, 6);

            for ($i = 0; $i < $recordCount; $i++) {
                // Random date between April 26, 2025 and now
                $startDate = Carbon::create(2025, 4, 26);
                $endDate = Carbon::now();
                $randomDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                // Select random waste item and data
                $wasteItem = $wasteItems->random();
                $wasteInfo = $wasteData[array_rand($wasteData)];
                $randomUnit = $wasteInfo['units'][array_rand($wasteInfo['units'])];

                // Generate realistic quantities based on unit
                $quantity = match ($randomUnit) {
                    'pieces' => rand(5, 50),
                    'kg' => rand(1, 10) + (rand(0, 9) / 10), // 1.0 to 10.9 kg
                    'sheets' => rand(10, 100),
                    default => rand(1, 20)
                };

                // Calculate points based on quantity and waste type
                $basePoints = $wasteInfo['points'];
                $multiplier = match ($randomUnit) {
                    'pieces' => max(1, intval($quantity / 5)), // 1 point per 5 pieces
                    'kg' => intval($quantity * 2), // 2 points per kg
                    'sheets' => max(1, intval($quantity / 10)), // 1 point per 10 sheets
                    default => max(1, intval($quantity / 2))
                };
                $totalPoints = $basePoints + $multiplier;

                // Select random recycling center
                $center = $recyclingCenters->random();

                DB::beginTransaction();

                try {
                    // Create recycling history entry
                    $history = RecyclingHistory::create([
                        'user_id' => $userId,
                        'center_id' => $center->id,
                        'waste_item_id' => $wasteItem->id,
                        'waste_name' => $wasteInfo['name'],
                        'quantity' => $quantity,
                        'unit' => $randomUnit,
                        'image' => null, // No image for seeded data
                        'created_at' => $randomDate,
                        'updated_at' => $randomDate,
                    ]);

                    // Create corresponding points transaction
                    $transaction = new PointsTransaction([
                        'user_id' => $userId,
                        'points' => $totalPoints,
                        'type' => 'earned',
                        'category' => 'recycling',
                        'description' => "Recycled {$quantity} {$randomUnit} of {$wasteInfo['name']}",
                        'center_id' => $center->id,
                        'created_at' => $randomDate,
                        'updated_at' => $randomDate,
                    ]);

                    // Save the transaction with the recycling history relationship
                    $history->pointsTransaction()->save($transaction);

                    DB::commit();

                    $this->command->info("Created recycling record for User {$userId}: {$quantity} {$randomUnit} of {$wasteInfo['name']} ({$totalPoints} points)");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error("Failed to create recycling record for User {$userId}: " . $e->getMessage());
                }
            }
        }

        $this->command->info('Recycling history seeding completed!');
    }
}
