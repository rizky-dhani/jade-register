<?php

namespace Database\Seeders;

use App\Models\HandsOnEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HandsOnEventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            // November 13, 2026
            [
                'name' => 'Dental Implant Workshop',
                'description' => 'Hands-on training on basic dental implant procedures and techniques.',
                'event_date' => '2026-11-13',
                'max_seats' => 30,
                'price' => 500000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Endodontic Microscopy',
                'description' => 'Practical training using dental microscopes for endodontic procedures.',
                'event_date' => '2026-11-13',
                'max_seats' => 30,
                'price' => 500000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Smile Design',
                'description' => 'Learn digital smile design concepts and hands-on software training.',
                'event_date' => '2026-11-13',
                'max_seats' => 30,
                'price' => 400000,
                'currency' => 'IDR',
                'is_active' => true,
            ],

            // November 14, 2026
            [
                'name' => 'Orthodontic Aligner Workshop',
                'description' => 'Hands-on training for clear aligner treatment planning and fabrication.',
                'event_date' => '2026-11-14',
                'max_seats' => 30,
                'price' => 500000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Periodontal Surgery Techniques',
                'description' => 'Practical training on periodontal surgical procedures.',
                'event_date' => '2026-11-14',
                'max_seats' => 30,
                'price' => 500000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'CAD/CAM Restoration',
                'description' => 'Hands-on training for digital impression and CAD/CAM restoration design.',
                'event_date' => '2026-11-14',
                'max_seats' => 30,
                'price' => 450000,
                'currency' => 'IDR',
                'is_active' => true,
            ],

            // November 15, 2026
            [
                'name' => 'Oral Surgery Basics',
                'description' => 'Fundamental oral surgery techniques and hands-on practice.',
                'event_date' => '2026-11-15',
                'max_seats' => 30,
                'price' => 500000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Pediatric Dentistry Workshop',
                'description' => 'Practical approach to pediatric dental procedures and behavior management.',
                'event_date' => '2026-11-15',
                'max_seats' => 30,
                'price' => 400000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Aesthetic Composite Restoration',
                'description' => 'Advanced techniques for aesthetic anterior composite restorations.',
                'event_date' => '2026-11-15',
                'max_seats' => 30,
                'price' => 450000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            HandsOnEvent::create($event);
        }
    }
}
