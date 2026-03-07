<?php

namespace App\Filament\Navigation;

enum NavigationGroup: string
{
    case REGISTRATIONS = 'Registrations';
    case USERS_PERMISSIONS = 'Users & Permissions';
    case SETTINGS = 'Settings';
}
