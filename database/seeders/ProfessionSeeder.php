<?php

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Seeder;

class ProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $professions = [
            ['name' => 'Dentist', 'sort_order' => 1],
            ['name' => 'Dental Student', 'sort_order' => 2],
            ['name' => 'Dental Hygienist', 'sort_order' => 3],
            ['name' => 'Dental Assistant', 'sort_order' => 4],
            ['name' => 'Dental Technician', 'sort_order' => 5],
            ['name' => 'Oral Surgeon', 'sort_order' => 6],
            ['name' => 'Orthodontist', 'sort_order' => 7],
            ['name' => 'Periodontist', 'sort_order' => 8],
            ['name' => 'Endodontist', 'sort_order' => 9],
            ['name' => 'Pediatric Dentist', 'sort_order' => 10],
            ['name' => 'Prosthodontist', 'sort_order' => 11],
            ['name' => 'Other', 'sort_order' => 12],
        ];

        foreach ($professions as $profession) {
            Profession::firstOrCreate(['name' => $profession['name']], $profession);
        }
    }
}
