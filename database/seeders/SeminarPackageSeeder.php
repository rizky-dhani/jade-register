<?php

namespace Database\Seeders;

use App\Models\SeminarPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeminarPackageSeeder extends Seeder
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
                'is_local' => true,
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
                'is_local' => true,
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
                'is_local' => true,
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
                'is_local' => true,
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
                'is_local' => false,
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
                'is_local' => false,
                'amount' => 200,
                'currency' => 'USD',
                'includes_lunch' => true,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($packages as $package) {
            SeminarPackage::updateOrCreate(
                ['code' => $package['code']],
                $package
            );
        }
    }
}
