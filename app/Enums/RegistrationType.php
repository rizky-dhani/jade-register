<?php

namespace App\Enums;

enum RegistrationType: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE => 'Online Registration',
            self::OFFLINE => 'On-site Registration',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ONLINE => 'Pre-event registration via website',
            self::OFFLINE => 'On-site registration at the venue',
        };
    }
}
