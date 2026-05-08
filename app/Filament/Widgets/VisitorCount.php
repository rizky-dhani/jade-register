<?php

namespace App\Filament\Widgets;

use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitorCount extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return __('filament.widgets.visitor_count');
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->hasRole('Participant');
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $unattendedDay1 = Visitor::whereDate('created_at', '2025-11-13')->whereNull('scanned_at')->count();
        $unattendedDay2 = Visitor::whereDate('created_at', '2025-11-14')->whereNull('scanned_at')->count();
        $unattendedDay3 = Visitor::whereDate('created_at', '2025-11-15')->whereNull('scanned_at')->count();

        $attendedDay1 = Visitor::whereDate('scanned_at', '2025-11-13')->count();
        $attendedDay2 = Visitor::whereDate('scanned_at', '2025-11-14')->count();
        $attendedDay3 = Visitor::whereDate('scanned_at', '2025-11-15')->count();

        return [
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 1]), (string) $unattendedDay1)
                ->description(__('filament.widgets.visitor_count.not_checked_in'))
                ->color('danger'),
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 2]), (string) $unattendedDay2)
                ->description(__('filament.widgets.visitor_count.not_checked_in'))
                ->color('danger'),
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 3]), (string) $unattendedDay3)
                ->description(__('filament.widgets.visitor_count.not_checked_in'))
                ->color('danger'),
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 1]), (string) $attendedDay1)
                ->description(__('filament.widgets.visitor_count.checked_in'))
                ->color('success'),
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 2]), (string) $attendedDay2)
                ->description(__('filament.widgets.visitor_count.checked_in'))
                ->color('success'),
            Stat::make(__('filament.widgets.visitor_count.day', ['day' => 3]), (string) $attendedDay3)
                ->description(__('filament.widgets.visitor_count.checked_in'))
                ->color('success'),
        ];
    }
}
