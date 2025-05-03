<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Recycling Beginner',
                'description' => 'Recycled your first 10 items.',
                'icon' => '🔰',
                'points_reward' => 50,
                'criteria' => [
                    'type' => 'recycling_count',
                    'threshold' => 10,
                ],
            ],
            [
                'name' => 'Recycling Pro',
                'description' => 'Recycled 50 items. You\'re making a real difference!',
                'icon' => '🏆',
                'points_reward' => 200,
                'criteria' => [
                    'type' => 'recycling_count',
                    'threshold' => 50,
                ],
            ],
            [
                'name' => 'Plastic Champion',
                'description' => 'Recycled 25 plastic items. The oceans thank you!',
                'icon' => '🐬',
                'points_reward' => 150,
                'criteria' => [
                    'type' => 'waste_type_count',
                    'waste_type' => 'Plastic',
                    'threshold' => 25,
                ],
            ],
            [
                'name' => 'Paper Saver',
                'description' => 'Recycled 25 paper items. You\'ve helped save trees!',
                'icon' => '🌲',
                'points_reward' => 150,
                'criteria' => [
                    'type' => 'waste_type_count',
                    'waste_type' => 'Paper',
                    'threshold' => 25,
                ],
            ],
            [
                'name' => 'Weekly Warrior',
                'description' => 'Recycled items for 4 consecutive weeks. Consistency is key!',
                'icon' => '📅',
                'points_reward' => 100,
                'criteria' => [
                    'type' => 'consecutive_weeks',
                    'threshold' => 4,
                ],
            ],
            [
                'name' => 'Variety Recycler',
                'description' => 'Recycled items from all 5 main categories. Diverse recycling!',
                'icon' => '🌈',
                'points_reward' => 250,
                'criteria' => [
                    'type' => 'diverse_categories',
                    'threshold' => 5,
                ],
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}
