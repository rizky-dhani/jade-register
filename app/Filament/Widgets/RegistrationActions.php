<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RegistrationActions extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.registration-actions';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('Participant') ?? false;
    }

    public function getHeading(): string
    {
        return 'Registration';
    }

    public function hasVerifiedSeminarRegistration(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->seminarRegistrations()
            ->where('payment_status', 'verified')
            ->exists();
    }
}
