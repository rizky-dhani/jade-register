<?php

namespace App\Filament\Widgets;

use App\Models\Visitor;
use Filament\Widgets\Widget;

class VisitorCountWidget extends Widget
{
    protected static string $view = 'filament.widgets.visitor-count-widget';

    public function getVisitorsData(): array
    {
        $day1 = Visitor::whereDate('created_at', '2025-11-13')->count();
        $day2 = Visitor::whereDate('created_at', '2025-11-14')->count();
        $day3 = Visitor::whereDate('created_at', '2025-11-15')->count();
        $total = Visitor::count();

        return [
            'day1' => $day1,
            'day2' => $day2,
            'day3' => $day3,
            'total' => $total,
        ];
    }
}
