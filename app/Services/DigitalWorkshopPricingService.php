<?php

namespace App\Services;

use App\Models\DigitalWorkshop;
use App\Models\SeminarRegistration;

class DigitalWorkshopPricingService
{
    /**
     * @return array{amount: int, registration_type: string, seminar_registration_id: int|null}
     */
    public function forEmail(string $email, DigitalWorkshop $workshop): array
    {
        $seminar = $this->eligibleSeminarRegistration($email);

        // A cleared bundle_price is null, and (int) null would be 0. Selling the
        // bundle for free is worse than selling it at the normal price.
        if (! $seminar || $workshop->bundle_price === null) {
            return [
                'amount' => (int) $workshop->price,
                'registration_type' => 'standalone',
                'seminar_registration_id' => null,
            ];
        }

        return [
            'amount' => (int) $workshop->bundle_price,
            'registration_type' => 'bundled',
            'seminar_registration_id' => $seminar->id,
        ];
    }

    public function eligibleSeminarRegistration(string $email): ?SeminarRegistration
    {
        return SeminarRegistration::whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->where('payment_status', 'verified')
            ->latest('id')
            ->first();
    }
}
