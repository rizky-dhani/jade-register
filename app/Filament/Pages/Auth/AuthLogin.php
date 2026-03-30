<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Section;

class AuthLogin extends BaseLogin
{
    protected function getBeforeFormComponents(): array
    {
        return [
            Section::make(__('seminar.login_alert_title'))
                ->description(__('seminar.login_alert_message'))
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->collapsed(false)
                ->aside(),
        ];
    }
}
