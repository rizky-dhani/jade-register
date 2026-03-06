<?php

namespace Database\Seeders;

use App\Models\MarketingSource;
use Illuminate\Database\Seeder;

class MarketingSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['name' => 'Social Media (Instagram)', 'sort_order' => 1],
            ['name' => 'Social Media (Facebook)', 'sort_order' => 2],
            ['name' => 'Social Media (LinkedIn)', 'sort_order' => 3],
            ['name' => 'Colleague/Friend', 'sort_order' => 4],
            ['name' => 'Email Campaign', 'sort_order' => 5],
            ['name' => 'Website', 'sort_order' => 6],
            ['name' => 'Dental Association', 'sort_order' => 7],
            ['name' => 'Google Search', 'sort_order' => 8],
            ['name' => 'Other', 'sort_order' => 9],
        ];

        foreach ($sources as $source) {
            MarketingSource::firstOrCreate(['name' => $source['name']], $source);
        }
    }
}
