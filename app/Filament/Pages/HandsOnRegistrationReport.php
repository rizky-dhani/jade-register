<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HandsOnParticipantCount;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HandsOnRegistrationReport extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Reporting';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = '';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.hands-on-registration-report';

    public static function getNavigationLabel(): string
    {
        return __('seminar.hands_on_registration_navigation');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Admin']) ?? false;
    }

    public function getHeading(): string
    {
        return __('seminar.hands_on_registration_report');
    }

    public function getSubheading(): string
    {
        return __('seminar.hands_on_registration_description');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HandsOnParticipantCount::class,
        ];
    }
}
