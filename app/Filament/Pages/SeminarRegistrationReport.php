<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SeminarRegistrations\Widgets\SeminarPackageChartWidget;
use App\Filament\Resources\SeminarRegistrations\Widgets\SeminarPackageStatsWidget;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SeminarRegistrationReport extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Reporting';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = '';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.seminar-registration-report';

    public static function getNavigationLabel(): string
    {
        return __('seminar.seminar_registration_navigation');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Admin']) ?? false;
    }

    public function getHeading(): string
    {
        return __('seminar.seminar_registration_report');
    }

    public function getSubheading(): string
    {
        return __('seminar.seminar_registration_description');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SeminarPackageStatsWidget::class,
            SeminarPackageChartWidget::class,
        ];
    }
}
