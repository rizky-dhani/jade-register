<?php

namespace App\Services;

use App\Enums\PricingTier;
use App\Enums\RegistrationType;
use App\Models\Country;

class PricingService
{
    public function getAvailableTiers(Country $country, RegistrationType $type): array
    {
        if ($country->is_local) {
            return PricingTier::getLocalTiers($type);
        }

        return PricingTier::getInternationalTiers($type);
    }

    public function calculatePrice(PricingTier $tier): int
    {
        return $tier->getPrice();
    }

    public function formatPrice(int $amount): string
    {
        return 'IDR '.number_format($amount, 0, ',', '.');
    }

    public function getTierByValue(string $value): PricingTier
    {
        return PricingTier::from($value);
    }
}
