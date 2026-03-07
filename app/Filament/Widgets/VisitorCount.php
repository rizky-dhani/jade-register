<?php

namespace App\Filament\Widgets;

use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitorCount extends StatsOverviewWidget
{
    protected ?string $heading = 'Visitors';

    protected function getStats(): array
    {
        $day1 = Visitor::whereDate('created_at', '2025-11-13')->count();
        $day2 = Visitor::whereDate('created_at', '2025-11-14')->count();
        $day3 = Visitor::whereDate('created_at', '2025-11-15')->count();
        $total = Visitor::count();

        return [
            Stat::make('Day 1', (string) $day1),
            Stat::make('Day 2', (string) $day2),
            Stat::make('Day 3', (string) $day3),
            Stat::make('Total', (string) $total),
        ];
    }
}
