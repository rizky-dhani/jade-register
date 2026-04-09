<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Addon::create([
            'name' => 'Lunch Add-On',
            'code' => 'LUNCH_ADDON',
            'description' => 'Add full lunch to your seminar package (includes main course, drink, and dessert).',
            'price' => 0, // TBD - update when pricing is decided
            'currency' => 'IDR',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
