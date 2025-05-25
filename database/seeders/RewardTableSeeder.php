<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reward;
use App\Models\RecyclingCenter;
use Illuminate\Support\Facades\DB;

class RewardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get approved recycling centers with IDs between 2-17
        $approvedCenters = RecyclingCenter::where('status', 'approved')
            ->whereBetween('id', [2, 17])
            ->pluck('id')
            ->toArray();

        // Reward Categories
        $rewardCategories = [
            'eco_product',
            'discount',
            'gift_card',
            'free_service',
            'donation',
            'merchandise',
        ];

        // Sample reward templates
        $rewardTemplates = [
            [
                'title' => 'Reusable Water Bottle',
                'description' => 'High-quality stainless steel water bottle to reduce plastic waste',
                'category' => 'eco_product',
                'points_cost' => 250,
                'terms' => 'Valid for pickup within 30 days of redemption',
                'redemption_instructions' => 'Present your redemption code at the center counter'
            ],
            [
                'title' => '10% Off Next Visit',
                'description' => 'Get 10% discount on your next recycling drop-off service fee',
                'category' => 'discount',
                'points_cost' => 100,
                'terms' => 'Cannot be combined with other offers',
                'redemption_instructions' => 'Show this coupon during your next visit'
            ],
            [
                'title' => 'Coffee Shop Gift Card',
                'description' => '$10 gift card for local eco-friendly coffee shops',
                'category' => 'gift_card',
                'points_cost' => 500,
                'terms' => 'Valid at participating locations only',
                'redemption_instructions' => 'Pick up physical gift card at center'
            ],
            [
                'title' => 'Free Electronics Pickup',
                'description' => 'Free home pickup service for large electronics recycling',
                'category' => 'free_service',
                'points_cost' => 300,
                'terms' => 'Limited to items under 50kg, within 10km radius',
                'redemption_instructions' => 'Call center to schedule pickup appointment'
            ],
            [
                'title' => 'Tree Planting Donation',
                'description' => 'Plant a tree in your name through our reforestation partner',
                'category' => 'donation',
                'points_cost' => 200,
                'terms' => 'Certificate will be emailed within 7 days',
                'redemption_instructions' => 'Automatic processing, no action required'
            ],
            [
                'title' => 'Eco-Friendly Tote Bag',
                'description' => 'Branded canvas tote bag made from recycled materials',
                'category' => 'merchandise',
                'points_cost' => 150,
                'terms' => 'While supplies last',
                'redemption_instructions' => 'Available for immediate pickup'
            ],
            [
                'title' => 'Bamboo Utensil Set',
                'description' => 'Portable bamboo utensil set with carrying case',
                'category' => 'eco_product',
                'points_cost' => 180,
                'terms' => 'Includes fork, knife, spoon, and chopsticks',
                'redemption_instructions' => 'Present redemption code at counter'
            ],
            [
                'title' => '20% Off Recycling Service',
                'description' => 'Get 20% off professional recycling service for businesses',
                'category' => 'discount',
                'points_cost' => 400,
                'terms' => 'Valid for business accounts only, minimum order $50',
                'redemption_instructions' => 'Quote redemption code when booking service'
            ],
            [
                'title' => 'Grocery Store Gift Card',
                'description' => '$25 gift card for sustainable grocery shopping',
                'category' => 'gift_card',
                'points_cost' => 800,
                'terms' => 'Valid at participating organic grocery stores',
                'redemption_instructions' => 'Pick up at center or request mail delivery'
            ],
            [
                'title' => 'Free Battery Recycling Kit',
                'description' => 'Home battery collection kit with prepaid shipping',
                'category' => 'free_service',
                'points_cost' => 120,
                'terms' => 'Includes collection container and shipping label',
                'redemption_instructions' => 'Kit will be mailed to your address'
            ],
            [
                'title' => 'Ocean Cleanup Donation',
                'description' => 'Support ocean plastic cleanup initiatives',
                'category' => 'donation',
                'points_cost' => 350,
                'terms' => 'Donation receipt provided for tax purposes',
                'redemption_instructions' => 'Automatic donation processing'
            ],
            [
                'title' => 'Recycled Notebook Set',
                'description' => 'Set of 3 notebooks made from 100% recycled paper',
                'category' => 'merchandise',
                'points_cost' => 90,
                'terms' => 'Various sizes included: A4, A5, and pocket size',
                'redemption_instructions' => 'Available at center reception'
            ]
        ];

        foreach ($approvedCenters as $centerId) {
            // Create 3-4 rewards per center
            $rewardCount = rand(3, 4);
            
            // Shuffle templates to get random selection
            $shuffledTemplates = collect($rewardTemplates)->shuffle();
            
            for ($i = 0; $i < $rewardCount; $i++) {
                $template = $shuffledTemplates[$i % count($rewardTemplates)];
                
                // Determine if reward should have expiry date (50% chance)
                $hasExpiry = rand(0, 1);
                $expiryDate = null;
                
                if ($hasExpiry) {
                    // Set expiry date between 1-6 months from now
                    $expiryDate = now()->addMonths(rand(1, 6));
                }
                
                // Determine quantity (some unlimited, some limited)
                $hasQuantity = rand(0, 1);
                $quantity = null;
                
                if ($hasQuantity) {
                    $quantity = rand(5, 50); // Limited quantity between 5-50
                }
                
                Reward::create([
                    'center_id' => $centerId,
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'category' => $template['category'],
                    'points_cost' => $template['points_cost'] + rand(-50, 100), // Add some variation
                    'quantity' => $quantity,
                    'expiry_date' => $expiryDate,
                    'image' => null, // You can add image paths if needed
                    'terms' => $template['terms'],
                    'redemption_instructions' => $template['redemption_instructions'],
                    'is_active' => true, // All rewards are active as requested
                    'is_featured' => rand(0, 1), // Random featured status
                ]);
            }
        }
    }
}