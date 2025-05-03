<?php

namespace Database\Seeders;

use App\Models\CleanupEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CleanupEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'Pantai Bersih Kuala Lumpur',
                'date' => '2025-04-20',
                'time' => '08:00 - 12:00',
                'location' => 'Pantai Dalam, Kuala Lumpur',
                'organizer' => 'Malaysian Marine Conservation Society',
                'description' => 'Join us for our monthly beach cleanup to protect our local marine ecosystem. Gloves and bags will be provided.',
                'latitude' => 3.1157,
                'longitude' => 101.6733,
                'website' => 'https://mmcs.org.my/events',
                'contact_number' => '+60123456789',
            ],
            [
                'title' => 'Sungai Klang Restoration Project',
                'date' => '2025-04-27',
                'time' => '09:00 - 14:00',
                'location' => 'Taman Melawati, Kuala Lumpur',
                'organizer' => 'River of Life',
                'description' => 'Help us clean the Klang River and learn about river ecosystem health. Volunteers will receive a free t-shirt and refreshments.',
                'latitude' => 3.2127,
                'longitude' => 101.7373,
                'website' => 'https://riveroflife.com.my',
                'contact_number' => '+60123789456',
            ],
            [
                'title' => 'Coastal Cleanup Penang',
                'date' => '2025-05-05',
                'time' => '07:30 - 11:30',
                'location' => 'Batu Ferringhi Beach, Penang',
                'organizer' => 'Trash Hero Penang',
                'description' => 'International Coastal Cleanup Day event at one of Penang\'s most beautiful beaches. Family-friendly event with educational activities.',
                'latitude' => 5.4789,
                'longitude' => 100.2399,
                'website' => 'https://trashhero.org/penang',
                'contact_number' => '+60174567890',
            ],
            [
                'title' => 'Mangrove Forest Conservation',
                'date' => '2025-05-12',
                'time' => '08:30 - 13:00',
                'location' => 'Matang Mangrove Forest, Perak',
                'organizer' => 'Malaysian Nature Society',
                'description' => 'Help clean up plastic waste from this vital ecosystem and learn about the importance of mangroves in coastal protection.',
                'latitude' => 4.85,
                'longitude' => 100.6333,
                'website' => 'https://www.mns.my',
                'contact_number' => '+60165432178',
            ],
        ];

        foreach ($events as $event) {
            CleanupEvent::create($event);
        }
    }
}
