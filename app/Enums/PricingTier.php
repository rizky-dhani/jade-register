<?php

namespace App\Enums;

enum PricingTier: string
{
    case ONLINE_LOCAL_SNACK_ONLY = 'online_local_snack_only';
    case ONLINE_LOCAL_SNACK_LUNCH = 'online_local_snack_lunch';
    case ONLINE_INTERNATIONAL_SNACK_LUNCH = 'online_international_snack_lunch';
    case OFFLINE_LOCAL_SNACK_LUNCH_1 = 'offline_local_snack_lunch_1';
    case OFFLINE_LOCAL_SNACK_LUNCH_2 = 'offline_local_snack_lunch_2';
    case OFFLINE_INTERNATIONAL_SNACK_LUNCH = 'offline_international_snack_lunch';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE_LOCAL_SNACK_ONLY => 'Online - Local - Snack Only',
            self::ONLINE_LOCAL_SNACK_LUNCH => 'Online - Local - Snack + Lunch',
            self::ONLINE_INTERNATIONAL_SNACK_LUNCH => 'Online - International - Snack + Lunch',
            self::OFFLINE_LOCAL_SNACK_LUNCH_1 => 'On-site - Local - Snack + Lunch (Tier 1)',
            self::OFFLINE_LOCAL_SNACK_LUNCH_2 => 'On-site - Local - Snack + Lunch (Tier 2)',
            self::OFFLINE_INTERNATIONAL_SNACK_LUNCH => 'On-site - International - Snack + Lunch',
        };
    }

    public function getPrice(): int
    {
        return match ($this) {
            self::ONLINE_LOCAL_SNACK_ONLY => 600000,
            self::ONLINE_LOCAL_SNACK_LUNCH => 900000,
            self::ONLINE_INTERNATIONAL_SNACK_LUNCH => 1500000,
            self::OFFLINE_LOCAL_SNACK_LUNCH_1 => 900000,
            self::OFFLINE_LOCAL_SNACK_LUNCH_2 => 1200000,
            self::OFFLINE_INTERNATIONAL_SNACK_LUNCH => 2500000,
        };
    }

    public function getFormattedPrice(): string
    {
        return 'IDR '.number_format($this->getPrice(), 0, ',', '.');
    }

    public function getMealPreference(): string
    {
        return match ($this) {
            self::ONLINE_LOCAL_SNACK_ONLY => 'Snack only',
            default => 'Snack + Lunch',
        };
    }

    public function isOnline(): bool
    {
        return $this->value.startsWith('online');
    }

    public function isOffline(): bool
    {
        return $this->value.startsWith('offline');
    }

    public function isLocal(): bool
    {
        return ! $this->isInternational();
    }

    public function isInternational(): bool
    {
        return str_contains($this->value, 'international');
    }

    public static function getOnlineTiers(): array
    {
        return [
            self::ONLINE_LOCAL_SNACK_ONLY,
            self::ONLINE_LOCAL_SNACK_LUNCH,
            self::ONLINE_INTERNATIONAL_SNACK_LUNCH,
        ];
    }

    public static function getOfflineTiers(): array
    {
        return [
            self::OFFLINE_LOCAL_SNACK_LUNCH_1,
            self::OFFLINE_LOCAL_SNACK_LUNCH_2,
            self::OFFLINE_INTERNATIONAL_SNACK_LUNCH,
        ];
    }

    public static function getLocalTiers(RegistrationType $type): array
    {
        return match ($type) {
            RegistrationType::ONLINE => [
                self::ONLINE_LOCAL_SNACK_ONLY,
                self::ONLINE_LOCAL_SNACK_LUNCH,
            ],
            RegistrationType::OFFLINE => [
                self::OFFLINE_LOCAL_SNACK_LUNCH_1,
                self::OFFLINE_LOCAL_SNACK_LUNCH_2,
            ],
        };
    }

    public static function getInternationalTiers(RegistrationType $type): array
    {
        return match ($type) {
            RegistrationType::ONLINE => [self::ONLINE_INTERNATIONAL_SNACK_LUNCH],
            RegistrationType::OFFLINE => [self::OFFLINE_INTERNATIONAL_SNACK_LUNCH],
        };
    }
}
