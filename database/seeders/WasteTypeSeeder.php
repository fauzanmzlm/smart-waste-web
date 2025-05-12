<?php

namespace Database\Seeders;

use App\Models\WasteType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WasteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wasteTypes = [
            // 1
            [
                'name' => 'Hazardous',
                'icon' => '🔋',
                'color' => '#607D8B',
                'description' => 'Hazardous waste includes items like batteries, chemicals, and other substances that can be harmful to the environment or human health if not properly disposed of.',
                'class' => 'hazardous',
                'tips' => 'Take hazardous waste to designated collection points. Never throw them in regular trash. Many electronics stores offer battery recycling programs.'
            ],
            // 2
            [
                'name' => 'Biological',
                'icon' => '🍎',
                'color' => '#2ecc71',
                'description' => 'Biological waste includes food scraps, yard trimmings, and other biodegradable materials that can be composted.',
                'class' => 'biological',
                'tips' => 'Start a compost bin for food scraps and yard waste. Keep meat and dairy products out of home compost piles to avoid pests.'
            ],
            // 3
            [
                'name' => 'Glass',
                'icon' => '🍷',
                'color' => '#2ecc71',
                'description' => 'Glass waste includes all types of glass containers like bottles, jars, and other glass items that can be recycled and reused.',
                'class' => 'glass',
                'tips' => 'Rinse thoroughly before recycling. Remove caps and lids. Glass is highly recyclable and can be made into new glass products.'
            ],
            // 4
            [
                'name' => 'Paper',
                'icon' => '📄',
                'color' => '#f1c40f',
                'description' => 'Paper waste includes newspapers, magazines, office paper, cardboard, and packaging materials. It is biodegradable and can be recycled several times.',
                'class' => 'paper',
                'tips' => 'Keep paper dry and clean. Remove any plastic coverings from magazines. Shredded paper can often be composted if it\'s not recyclable in your area.'
            ],
            // 5
            [
                'name' => 'Textiles',
                'icon' => '👗',
                'color' => '#FF4081',
                'description' => 'Textile waste includes old clothes, fabrics, shoes, and other materials that can be reused, repurposed, or recycled.',
                'class' => 'textiles',
                'tips' => 'Donate wearable items to charity. Use old t-shirts as cleaning rags. Some companies have take-back programs for recycling old textiles.'
            ],
            // 6
            [
                'name' => 'Metal',
                'icon' => '🥫',
                'color' => '#95a5a6',
                'description' => 'Metal waste includes aluminum cans, steel containers, scrap metal, and other metallic items that can be melted down and reused.',
                'class' => 'metal',
                'tips' => 'Rinse food containers. Crush aluminum cans to save space. Metal is infinitely recyclable without loss of quality.'
            ],
            // 7
            [
                'name' => 'Plastic',
                'icon' => '🧴',
                'color' => '#3498db',
                'description' => 'Plastic waste includes materials like bottles, packaging, bags, and containers made from synthetic polymers.',
                'class' => 'plastic',
                'tips' => 'Check the recycling number (1-7) on the bottom of containers to determine recyclability in your area. Rinse containers and remove caps.'
            ],
            // 8
            [
                'name' => 'General Waste',
                'icon' => '🗑️',
                'color' => '#757575',
                'description' => 'General waste refers to non-recyclable materials or waste that does not fit into other specific categories. It typically includes miscellaneous items that need to be disposed of.',
                'class' => 'general-waste',
                'tips' => 'Try to minimize general waste by properly sorting recyclables. Consider if items can be repurposed before throwing them away.'
            ],
            // 9
            [
                'name' => 'Electronic Waste',
                'icon' => '💻',
                'color' => '#FF6347',
                'description' => 'E-waste includes discarded electronic devices like computers, phones, and batteries.',
                'class' => 'e-waste',
                'tips' => 'Recycle electronics at certified e-waste facilities. Don\'t throw old electronics in the trash as they contain harmful substances.'
            ],
            // 10
            [
                'name' => 'Non Waste',
                'icon' => '♻️',
                'color' => '#1abc9c',
                'description' => 'Non-waste refers to items that are either reusable, repurposed, or do not require disposal.',
                'class' => 'non-waste',
                'tips' => 'Focus on reusing and repurposing materials rather than discarding them. Look for sustainable alternatives to reduce waste.'
            ]
        ];

        foreach ($wasteTypes as $type) {
            WasteType::create($type);
        }
    }
}
