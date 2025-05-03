<?php

namespace Database\Seeders;

use App\Models\WasteItem;
use App\Models\WasteType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WasteItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wasteItems = [
            // Battery
            [
                'waste_type_id' => 1,
                'name' => 'AA Battery',
                'description' => 'Common household batteries used in devices like remote controls and toys.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Take to a designated recycling point',
                    'Do not throw in regular trash',
                    'Store in a battery collection bin until recycling',
                ],
                'restrictions' => 'Batteries contain chemicals that can be harmful if disposed of incorrectly.',
                'alternatives' => 'Use rechargeable batteries to reduce waste.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 1,
                'name' => 'Lithium-ion Battery',
                'description' => 'Rechargeable batteries commonly used in electronics like smartphones and laptops.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Take to an electronics recycling center',
                    'Do not throw in the trash',
                    'Keep batteries away from fire',
                ],
                'restrictions' => 'Improper disposal can lead to fire or toxic chemical release.',
                'alternatives' => 'Opt for devices with removable batteries or those using renewable energy.',
                'points' => 20,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 1,
                'name' => 'Car Battery',
                'description' => 'Used batteries from vehicles, containing toxic chemicals like lead and acid.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Take to a car battery recycling facility',
                    'Do not dispose of in regular waste',
                    'Keep the battery upright to avoid leaks',
                ],
                'restrictions' => 'Requires professional recycling due to hazardous materials.',
                'alternatives' => 'Consider electric vehicles with recyclable battery systems.',
                'points' => 25,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            // Biological
            [
                'waste_type_id' => 2,
                'name' => 'Food Scraps',
                'description' => 'Leftover food from meals, fruits, and vegetables.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Place in a compost bin',
                    'Avoid adding meat or dairy',
                    'Shred larger food scraps to accelerate composting',
                ],
                'restrictions' => 'Do not compost meat or dairy items.',
                'alternatives' => 'Use compostable plates or reduce food waste by planning meals better.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 2,
                'name' => 'Yard Waste',
                'description' => 'Grass clippings, leaves, and small twigs collected from the yard.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Place in compost or yard waste bin',
                    'Shred leaves for quicker composting',
                    'Avoid adding toxic plants like ivy or poison ivy',
                ],
                'restrictions' => 'Do not add invasive species or weeds that might spread.',
                'alternatives' => 'Mulch yard waste to nourish the soil.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 2,
                'name' => 'Eggshells',
                'description' => 'Eggshells from used eggs, which are biodegradable and can be composted.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Place in compost bin or garden soil',
                    'Crush eggshells before composting',
                ],
                'restrictions' => 'Do not use large quantities in compost; they should be balanced with other organic matter.',
                'alternatives' => 'Use eggshells to deter pests in gardens.',
                'points' => 8,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            // Brown Glass
            [
                'waste_type_id' => 3,
                'name' => 'Beer Bottle',
                'description' => 'Empty brown glass bottles used for beverages like beer.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle thoroughly',
                    'Remove the cap and label',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Some bottle labels may not be recyclable; check local guidelines.',
                'alternatives' => 'Opt for reusable glass bottles or cans.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.03,
                ],
            ],
            [
                'waste_type_id' => 3,
                'name' => 'Wine Bottle',
                'description' => 'Brown glass bottles commonly used for storing wine.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle',
                    'Remove any corks or metal caps',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Ensure the bottle is clean before recycling to prevent contamination.',
                'alternatives' => 'Consider buying wine in bulk or from eco-friendly producers.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.04,
                ],
            ],
            [
                'waste_type_id' => 3,
                'name' => 'Spice Jar',
                'description' => 'Small brown glass jars used for storing spices.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Clean the jar before recycling',
                    'Remove any metal or plastic lids',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Remove labels to improve recycling efficiency.',
                'alternatives' => 'Use refillable spice jars made from stainless steel or bamboo.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.02,
                ],
            ],
            // Cardboard
            [
                'waste_type_id' => 4,
                'name' => 'Shipping Box',
                'description' => 'Used cardboard boxes for shipping, often containing packaging material.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Flatten the box before recycling',
                    'Remove any plastic or styrofoam inserts',
                    'Keep the box dry',
                ],
                'restrictions' => 'Do not include boxes with food or grease stains.',
                'alternatives' => 'Use reusable shipping containers or sustainable packaging.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 4,
                'name' => 'Cardboard Tubes',
                'description' => 'Empty cardboard tubes from products like toilet paper or paper towels.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Place in cardboard recycling bin',
                    'Do not include tubes with tape or plastic lining',
                ],
                'restrictions' => 'Avoid placing tubes with excessive adhesive or wrapping.',
                'alternatives' => 'Choose paper towel alternatives to reduce waste.',
                'points' => 8,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            [
                'waste_type_id' => 4,
                'name' => 'Cereal Box',
                'description' => 'Cardboard packaging used for cereals and other dry food products.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Flatten and remove plastic liner',
                    'Place in cardboard recycling bin',
                ],
                'restrictions' => 'Ensure the box is clean and dry before recycling.',
                'alternatives' => 'Use bulk bins to reduce the need for cardboard packaging.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0,
                ],
            ],
            // Clothes
            [
                'waste_type_id' => 5,
                'name' => 'Old T-shirt',
                'description' => 'Worn-out t-shirts that can no longer be worn.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if wearable',
                    'Use as cleaning rags if not wearable',
                    'Look for textile recycling programs',
                ],
                'restrictions' => 'Ensure clothes are clean before donating or recycling.',
                'alternatives' => 'Opt for clothing made from natural fibers that decompose more easily.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0.1,
                    'marineLifeSavedFactor' => 0.02,
                ],
            ],
            [
                'waste_type_id' => 5,
                'name' => 'Jeans',
                'description' => 'Old or damaged denim jeans that can no longer be worn.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if in wearable condition',
                    'Repurpose as patches or fabric for other projects',
                    'Look for textile recycling programs',
                ],
                'restrictions' => 'Remove buttons, zippers, and other non-fabric parts before recycling.',
                'alternatives' => 'Choose jeans made from sustainable or recycled materials.',
                'points' => 18,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0.2,
                    'marineLifeSavedFactor' => 0.03,
                ],
            ],
            [
                'waste_type_id' => 5,
                'name' => 'Sweater',
                'description' => 'Old sweaters that are no longer in use.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if in good condition',
                    'Repurpose for craft projects',
                    'Recycle through a textile recycling program',
                ],
                'restrictions' => 'Remove non-fabric components like buttons or zippers.',
                'alternatives' => 'Buy second-hand clothing to reduce textile waste.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0.15,
                    'marineLifeSavedFactor' => 0.04,
                ],
            ],
            // Green Glass
            [
                'waste_type_id' => 6,
                'name' => 'Wine Bottle',
                'description' => 'Green glass bottles used for storing wine.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle',
                    'Remove any cork or metal caps',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Ensure the bottle is clean to avoid contamination of other recyclables.',
                'alternatives' => 'Opt for eco-friendly wine packaging like bag-in-box or refillable bottles.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.05,
                ],
            ],
            [
                'waste_type_id' => 6,
                'name' => 'Olive Oil Bottle',
                'description' => 'Green glass bottles used for storing olive oil and other cooking oils.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle thoroughly',
                    'Remove the cap or lid',
                    'Place in the glass recycling bin',
                ],
                'restrictions' => 'Avoid putting bottles with any residual oil into the recycling bin.',
                'alternatives' => 'Use bulk refillable oil containers to minimize waste.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.04,
                ],
            ],
            [
                'waste_type_id' => 6,
                'name' => 'Green Glass Jar',
                'description' => 'Glass jars made from green glass, typically used for preserves or sauces.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Clean the jar and lid',
                    'Place in glass recycling bin',
                    'Remove any plastic liners if present',
                ],
                'restrictions' => 'Make sure the glass is free of food remnants before recycling.',
                'alternatives' => 'Repurpose jars for storage or craft projects.',
                'points' => 14,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.03,
                ],
            ],
            // Metal
            [
                'waste_type_id' => 7,
                'name' => 'Aluminum Can',
                'description' => 'Cans made of aluminum used for beverages like soda and energy drinks.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the can before recycling',
                    'Flatten the can to save space',
                    'Place in metal recycling bin',
                ],
                'restrictions' => 'Ensure no liquids or food residues remain in the can.',
                'alternatives' => 'Use reusable containers made of stainless steel or glass.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.05,
                ],
            ],
            [
                'waste_type_id' => 7,
                'name' => 'Steel Can',
                'description' => 'Steel cans commonly used for food products like soup, beans, and vegetables.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the can thoroughly',
                    'Remove the lid and place it in metal recycling',
                    'Flatten the can to reduce space usage',
                ],
                'restrictions' => 'Ensure the can is free of food residues and grease.',
                'alternatives' => 'Use reusable or glass containers for food storage.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.04,
                ],
            ],
            [
                'waste_type_id' => 7,
                'name' => 'Scrap Metal',
                'description' => 'Leftover metal parts, like old tools or scrap from construction.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Take to a scrap metal recycling center',
                    'Clean the metal if possible',
                    'Separate different types of metal for better recycling',
                ],
                'restrictions' => 'Avoid mixing metals with other materials like plastic or wood.',
                'alternatives' => 'Repurpose scrap metal for DIY projects or upcycling.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.06,
                ],
            ],
            // Paper
            [
                'waste_type_id' => 8,
                'name' => 'Newspaper',
                'description' => 'Old newspapers used for reading or packaging.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Place in the paper recycling bin',
                    'Remove any plastic coverings or inserts',
                    'Keep the paper dry and clean',
                ],
                'restrictions' => 'Do not include newspapers with excessive ink or glossy finishes.',
                'alternatives' => 'Switch to digital news sources to reduce paper waste.',
                'points' => 8,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.02,
                ],
            ],
            [
                'waste_type_id' => 8,
                'name' => 'Magazines',
                'description' => 'Old magazines that are no longer needed.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Remove any plastic inserts or covers',
                    'Place in the paper recycling bin',
                    'Ensure the paper is clean and dry',
                ],
                'restrictions' => 'Do not recycle magazines with excessive plastic or wax coatings.',
                'alternatives' => 'Read magazines digitally or subscribe to eco-friendly magazines.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.03,
                ],
            ],
            [
                'waste_type_id' => 8,
                'name' => 'Cardboard Packaging',
                'description' => 'Used cardboard boxes for packaging products like electronics or food.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Flatten the box',
                    'Remove any plastic or styrofoam',
                    'Place in cardboard recycling bin',
                ],
                'restrictions' => 'Do not include boxes with food stains or heavy plastic wrapping.',
                'alternatives' => 'Use reusable packaging or buy products with minimal packaging.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.01,
                ],
            ],
            // Plastic
            [
                'waste_type_id' => 9,
                'name' => 'Plastic Bottle',
                'description' => 'Single-use plastic bottles for water, soda, or other beverages.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Empty and rinse the bottle',
                    'Remove the cap (can be recycled separately)',
                    'Remove any labels if possible',
                    'Crush to save space',
                ],
                'restrictions' => 'Not all plastic bottles are recyclable. Look for recycling symbols 1 (PET) and 2 (HDPE).',
                'alternatives' => 'Use reusable water bottles made of stainless steel or glass.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 20,
                    'microplasticsPreventionFactor' => 1.5,
                    'marineLifeSavedFactor' => 0.05,
                ],
            ],
            [
                'waste_type_id' => 9,
                'name' => 'Plastic Bag',
                'description' => 'Single-use plastic bags often used for groceries.',
                'recyclable' => false,
                'disposal_instructions' => [
                    'Do not throw in the trash',
                    'Take to a special recycling center for plastic bags',
                    'Consider reusable cloth bags as an alternative',
                ],
                'restrictions' => 'Plastic bags can contaminate recycling facilities and are not always recyclable.',
                'alternatives' => 'Use reusable shopping bags made of cloth or jute.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 50,
                    'microplasticsPreventionFactor' => 2,
                    'marineLifeSavedFactor' => 0.1,
                ],
            ],
            [
                'waste_type_id' => 9,
                'name' => 'Plastic Straws',
                'description' => 'Single-use plastic straws used for drinking.',
                'recyclable' => false,
                'disposal_instructions' => [
                    'Avoid using plastic straws whenever possible',
                    'Opt for reusable metal, glass, or bamboo straws',
                ],
                'restrictions' => 'Plastic straws are difficult to recycle and pose a risk to marine life.',
                'alternatives' => 'Use reusable straws made of stainless steel, glass, or bamboo.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 100,
                    'microplasticsPreventionFactor' => 3,
                    'marineLifeSavedFactor' => 0.2,
                ],
            ],
            // Shoes
            [
                'waste_type_id' => 10,
                'name' => 'Old Sneakers',
                'description' => 'Worn-out athletic shoes that are no longer wearable.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if in wearable condition',
                    'Look for shoe recycling programs offered by brands or stores',
                    'Repurpose shoes for gardening or other DIY projects',
                ],
                'restrictions' => 'Remove any non-fabric parts like laces, buckles, or rubber soles before recycling.',
                'alternatives' => 'Choose shoes made from sustainable materials like recycled plastic or natural fibers.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 10,
                    'microplasticsPreventionFactor' => 0.5,
                    'marineLifeSavedFactor' => 0.1,
                ],
            ],
            [
                'waste_type_id' => 10,
                'name' => 'Leather Boots',
                'description' => 'Old leather boots that have worn out over time.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if in good condition',
                    'Use as gardening shoes or repurpose the leather',
                    'Look for specialized leather recycling programs',
                ],
                'restrictions' => 'Remove any metal components, such as zippers or buckles, before recycling.',
                'alternatives' => 'Opt for vegan or sustainable alternatives made from eco-friendly materials.',
                'points' => 18,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0.3,
                    'marineLifeSavedFactor' => 0.05,
                ],
            ],
            [
                'waste_type_id' => 10,
                'name' => 'Worn-out Sandals',
                'description' => 'Old sandals that are no longer comfortable or suitable for wear.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate if in wearable condition',
                    'Recycle through specialized shoe recycling programs',
                    'Use as DIY projects or for arts and crafts',
                ],
                'restrictions' => 'Remove any non-recyclable components like rubber or plastic straps.',
                'alternatives' => 'Opt for sandals made from recycled materials or eco-friendly alternatives.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 5,
                    'microplasticsPreventionFactor' => 0.4,
                    'marineLifeSavedFactor' => 0.08,
                ],
            ],
            // Trash
            [
                'waste_type_id' => 11,
                'name' => 'Packaging Waste',
                'description' => 'Excess packaging from food, electronics, or other products that are difficult to recycle.',
                'recyclable' => false,
                'disposal_instructions' => [
                    'Sort out recyclables like plastics and paper from packaging waste',
                    'Reduce the use of packaging by buying in bulk or choosing minimal packaging options',
                ],
                'restrictions' => 'Packaging materials like bubble wrap and styrofoam are not recyclable.',
                'alternatives' => 'Switch to products with minimal or biodegradable packaging.',
                'points' => 5,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 50,
                    'microplasticsPreventionFactor' => 2.5,
                    'marineLifeSavedFactor' => 0.15,
                ],
            ],
            [
                'waste_type_id' => 11,
                'name' => 'Broken Furniture',
                'description' => 'Old or broken furniture that can’t be reused or repaired.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Donate parts that are in good condition',
                    'Check with local recycling centers for furniture recycling options',
                    'Repurpose or upcycle parts like wood or metal for DIY projects',
                ],
                'restrictions' => 'Do not dispose of treated or painted wood unless the center accepts it.',
                'alternatives' => 'Buy second-hand or sustainably produced furniture to reduce waste.',
                'points' => 20,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.1,
                ],
            ],
            [
                'waste_type_id' => 11,
                'name' => 'Non-recyclable Plastics',
                'description' => 'Plastic items that cannot be recycled due to mixed materials or lack of recycling programs.',
                'recyclable' => false,
                'disposal_instructions' => [
                    'Avoid purchasing single-use plastics',
                    'Look for waste-to-energy disposal options if available',
                ],
                'restrictions' => 'Many non-recyclable plastics, like multi-layered packaging, cannot be processed in regular recycling systems.',
                'alternatives' => 'Choose reusable or compostable options instead of plastic.',
                'points' => 8,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 100,
                    'microplasticsPreventionFactor' => 3,
                    'marineLifeSavedFactor' => 0.2,
                ],
            ],
            // White Glass
            [
                'waste_type_id' => 12,
                'name' => 'Clear Wine Bottle',
                'description' => 'Clear glass bottles used for wine or spirits.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle before recycling',
                    'Remove the cork or metal cap',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Ensure the bottle is clean and free from residue.',
                'alternatives' => 'Buy wine in bulk or from brands offering eco-friendly packaging.',
                'points' => 10,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.04,
                ],
            ],
            [
                'waste_type_id' => 12,
                'name' => 'Glass Jar',
                'description' => 'Clear glass jars often used for food products like jam, honey, and sauces.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Clean the jar and remove the lid',
                    'Place in glass recycling bin',
                    'Remove any labels if possible',
                ],
                'restrictions' => 'Ensure the jar is clean and free of food residues before recycling.',
                'alternatives' => 'Reuse jars for storage or DIY craft projects.',
                'points' => 12,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.03,
                ],
            ],
            [
                'waste_type_id' => 12,
                'name' => 'Clear Glass Bottle',
                'description' => 'Clear glass bottles typically used for beverages like juices and water.',
                'recyclable' => true,
                'disposal_instructions' => [
                    'Rinse the bottle',
                    'Remove the cap and labels',
                    'Place in glass recycling bin',
                ],
                'restrictions' => 'Do not recycle bottles with any toxic or non-recyclable substances.',
                'alternatives' => 'Choose refillable glass bottles to reduce waste.',
                'points' => 15,
                'ocean_impact_factors' => [
                    'oceanPlasticSaved' => 0,
                    'microplasticsPreventionFactor' => 0,
                    'marineLifeSavedFactor' => 0.05,
                ],
            ],
        ];

        // Insert all items into the database
        foreach ($wasteItems as $wasteItem) {
            WasteItem::create($wasteItem);
        }
    }
}
