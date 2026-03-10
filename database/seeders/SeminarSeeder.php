<?php

namespace Database\Seeders;

use App\Models\Seminar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeminarSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            // Local packages (Indonesia)
            [
                'name' => 'Early Bird - Snack Only',
                'code' => 'local_early_bird_snack',
                'description' => 'Early bird pricing for local participants with snack package only',
                'applies_to' => 'local',
                'amount' => 600000,
                'currency' => 'IDR',
                'includes_lunch' => false,
                'is_early_bird' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Early Bird - Snack + Lunch',
                'code' => 'local_early_bird_lunch',
                'description' => 'Early bird pricing for local participants with snack and lunch package',
                'applies_to' => 'local',
                'amount' => 900000,
                'currency' => 'IDR',
                'includes_lunch' => true,
                'is_early_bird' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Regular - Snack Only',
                'code' => 'local_regular_snack',
                'description' => 'Regular pricing for local participants with snack package only',
                'applies_to' => 'local',
                'amount' => 900000,
                'currency' => 'IDR',
                'includes_lunch' => false,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Regular - Snack + Lunch',
                'code' => 'local_regular_lunch',
                'description' => 'Regular pricing for local participants with snack and lunch package',
                'applies_to' => 'local',
                'amount' => 1200000,
                'currency' => 'IDR',
                'includes_lunch' => true,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],

            // International packages
            [
                'name' => 'Early Bird',
                'code' => 'intl_early_bird',
                'description' => 'Early bird pricing for international participants',
                'applies_to' => 'international',
                'amount' => 150,
                'currency' => 'USD',
                'includes_lunch' => true,
                'is_early_bird' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Regular',
                'code' => 'intl_regular',
                'description' => 'Regular pricing for international participants',
                'applies_to' => 'international',
                'amount' => 200,
                'currency' => 'USD',
                'includes_lunch' => true,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],

            // Universal package (applies to both)
            [
                'name' => 'Special Package - All Inclusive',
                'code' => 'universal_special',
                'description' => 'Special package available to all participants regardless of country',
                'applies_to' => 'all',
                'amount' => 250,
                'currency' => 'USD',
                'includes_lunch' => true,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($packages as $package) {
            Seminar::updateOrCreate(
                ['code' => $package['code']],
                $package
            );
        }
    }
}
