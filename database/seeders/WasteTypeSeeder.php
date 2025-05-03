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
            [
                'name' => 'Battery',
                'icon' => '🔋',
                'color' => '#607D8B',
                'description' => 'Battery waste includes disposable and rechargeable batteries that contain chemicals that can be harmful to the environment if not properly disposed of.',
                'class' => 'battery',
                'tips' => 'Take batteries to designated collection points. Never throw them in regular trash. Many electronics stores offer battery recycling programs.'
            ],
            [
                'name' => 'Biological',
                'icon' => '🍎',
                'color' => '#2ecc71',
                'description' => 'Biological waste includes food scraps, yard trimmings, and other biodegradable materials that can be composted.',
                'class' => 'biological',
                'tips' => 'Start a compost bin for food scraps and yard waste. Keep meat and dairy products out of home compost piles to avoid pests.'
            ],
            [
                'name' => 'Brown Glass',
                'icon' => '🍸',
                'color' => '#8e44ad',
                'description' => 'Brown glass waste includes bottles, jars, and other items made from brown glass that can be recycled and reused.',
                'class' => 'brown-glass',
                'tips' => 'Rinse containers before recycling. Remove caps and lids. Brown glass is often used for beer bottles and some medicine containers.'
            ],
            [
                'name' => 'Cardboard',
                'icon' => '📦',
                'color' => '#f39c12',
                'description' => 'Cardboard waste includes packaging materials, shipping boxes, and other items made from cardboard that can be recycled.',
                'class' => 'cardboard',
                'tips' => 'Flatten boxes to save space. Remove any plastic or styrofoam inserts. Keep cardboard dry to maintain recyclability.'
            ],
            [
                'name' => 'Clothes',
                'icon' => '👗',
                'color' => '#FF4081',
                'description' => 'Clothing waste includes old clothes and fabrics that can be reused, repurposed, or recycled.',
                'class' => 'clothes',
                'tips' => 'Donate wearable items to charity. Use old t-shirts as cleaning rags. Look for textile recycling programs for unwearable items.'
            ],
            [
                'name' => 'Green Glass',
                'icon' => '🍷',
                'color' => '#2ecc71',
                'description' => 'Green glass waste includes bottles, jars, and other items made from green glass that can be recycled and reused.',
                'class' => 'green-glass',
                'tips' => 'Rinse thoroughly before recycling. Green glass is commonly used for wine bottles and some food containers.'
            ],
            [
                'name' => 'Metal',
                'icon' => '🥫',
                'color' => '#95a5a6',
                'description' => 'Metal waste includes aluminum cans, steel containers, scrap metal, and other metallic items that can be melted down and reused.',
                'class' => 'metal',
                'tips' => 'Rinse food containers. Crush aluminum cans to save space. Metal is infinitely recyclable without loss of quality.'
            ],
            [
                'name' => 'Paper',
                'icon' => '📄',
                'color' => '#f1c40f',
                'description' => 'Paper waste includes newspapers, magazines, office paper, cardboard, and packaging materials. It is biodegradable and can be recycled several times.',
                'class' => 'paper',
                'tips' => 'Keep paper dry and clean. Remove any plastic coverings from magazines. Shredded paper can often be composted if it\'s not recyclable in your area.'
            ],
            [
                'name' => 'Plastic',
                'icon' => '🧴',
                'color' => '#3498db',
                'description' => 'Plastic waste includes materials like bottles, packaging, bags, and containers made from synthetic polymers.',
                'class' => 'plastic',
                'tips' => 'Check the recycling number (1-7) on the bottom of containers to determine recyclability in your area. Rinse containers and remove caps.'
            ],
            [
                'name' => 'Shoes',
                'icon' => '👟',
                'color' => '#7B1FA2',
                'description' => 'Old shoes and footwear that can be reused, repaired, or recycled.',
                'class' => 'shoes',
                'tips' => 'Donate wearable shoes to charity. Some athletic shoe companies have take-back programs for recycling old sneakers.'
            ],
            [
                'name' => 'Trash',
                'icon' => '🗑️',
                'color' => '#757575',
                'description' => 'Miscellaneous waste that doesn\'t fit into other categories.',
                'class' => 'trash',
                'tips' => 'Try to minimize general waste by properly sorting recyclables. Consider if items can be repurposed before throwing them away.'
            ],
            [
                'name' => 'White Glass',
                'icon' => '🍷',
                'color' => '#bdc3c7',
                'description' => 'White glass waste includes bottles, jars, and other items made from white glass that can be recycled and reused.',
                'class' => 'white-glass',
                'tips' => 'Rinse containers thoroughly. Remove metal lids. Clear glass is highly recyclable and can be made into new glass products.'
            ]
        ];

        foreach ($wasteTypes as $type) {
            WasteType::create($type);
        }
    }
}
