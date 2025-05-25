<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RewardRedemption;
use App\Models\PointsTransaction;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RewardRedemptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = [18, 19, 20];
        $statuses = ['pending', 'approved', 'rejected'];
        
        // Get all available rewards
        $rewards = Reward::where('is_active', true)->get();
        
        if ($rewards->isEmpty()) {
            $this->command->info('No active rewards found. Please run RewardTableSeeder first.');
            return;
        }

        foreach ($userIds as $userId) {
            // Check if user exists
            $user = User::find($userId);
            if (!$user) {
                $this->command->info("User with ID {$userId} not found. Skipping...");
                continue;
            }

            // Create 3-4 redemptions per user
            $redemptionCount = rand(3, 4);
            
            // Get random rewards for this user
            $userRewards = $rewards->shuffle()->take($redemptionCount);
            
            foreach ($userRewards as $reward) {
                // Random status
                $status = $statuses[array_rand($statuses)];
                
                // Random creation date (within last 3 months)
                $createdAt = Carbon::now()->subDays(rand(1, 90));
                
                DB::beginTransaction();
                
                try {
                    // Create redemption
                    $redemption = new RewardRedemption([
                        'user_id' => $userId,
                        'reward_id' => $reward->id,
                        'status' => $status,
                        'points_cost' => $reward->points_cost,
                        'notes' => $this->getRandomNotes($status),
                    ]);
                    
                    // Set creation date
                    $redemption->created_at = $createdAt;
                    $redemption->updated_at = $createdAt;
                    
                    // If processed, set processed date and processor
                    if ($status !== 'pending') {
                        $redemption->processed_at = $createdAt->addHours(rand(1, 48));
                        $redemption->processed_by = $this->getRandomProcessor();
                    }
                    
                    $redemption->save();

                    // Create points transaction (always deduct points initially)
                    $transaction = new PointsTransaction([
                        'user_id' => $userId,
                        'points' => $reward->points_cost,
                        'type' => PointsTransaction::TYPE_SPENT,
                        'category' => PointsTransaction::CATEGORY_REWARD_REDEMPTION,
                        'description' => "Redeemed reward: {$reward->title}",
                        'center_id' => $reward->center_id,
                    ]);
                    
                    // Set transaction creation date
                    $transaction->created_at = $createdAt;
                    $transaction->updated_at = $createdAt;
                    
                    $redemption->pointsTransaction()->save($transaction);
                    
                    // If redemption was rejected, create refund transaction
                    if ($status === 'rejected') {
                        $refundTransaction = PointsTransaction::create([
                            'user_id' => $userId,
                            'points' => $reward->points_cost,
                            'type' => PointsTransaction::TYPE_EARNED,
                            'category' => PointsTransaction::CATEGORY_REFUND,
                            'description' => "Refund for rejected reward: {$reward->title}",
                            'center_id' => $reward->center_id,
                            'transactionable_id' => $redemption->id,
                            'transactionable_type' => RewardRedemption::class,
                            'created_at' => $redemption->processed_at->addMinutes(5),
                            'updated_at' => $redemption->processed_at->addMinutes(5),
                        ]);
                    }

                    DB::commit();
                    
                    $this->command->info("Created {$status} redemption for User {$userId}: {$reward->title}");
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error("Failed to create redemption for User {$userId}: " . $e->getMessage());
                }
                
                // Small delay to ensure different timestamps
                usleep(100000); // 0.1 second
            }
        }
    }
    
    /**
     * Get random notes based on status
     */
    private function getRandomNotes($status)
    {
        $notes = [
            'pending' => [
                null, // Some pending might not have notes
                'Waiting for verification',
                'Under review',
            ],
            'approved' => [
                'Approved and ready for pickup',
                'Reward processed successfully',
                'Verified and approved',
                'Ready for collection at center',
                'Approved - valid redemption',
                null, // Some approved might not have notes
            ],
            'rejected' => [
                'Invalid redemption code',
                'Reward no longer available',
                'User did not meet redemption requirements',
                'Technical issue during processing',
                'Insufficient verification provided',
                'Expired reward claimed',
            ]
        ];
        
        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
    
    /**
     * Get random processor (staff member)
     * You might want to adjust this based on your actual staff user IDs
     */
    private function getRandomProcessor()
    {
        // Assuming staff members have IDs 1-10, adjust as needed
        $staffIds = range(1, 10);
        return $staffIds[array_rand($staffIds)];
    }
}