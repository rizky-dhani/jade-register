<?php

namespace App\Services;

use App\Models\Setting;

class LocationService
{
    public function isUserOnSite(float $userLat, float $userLng): bool
    {
        $venueCoords = $this->getVenueCoordinates();
        $radius = $this->getDetectionRadius();

        $distance = $this->calculateDistance(
            $userLat,
            $userLng,
            $venueCoords['lat'],
            $venueCoords['lng']
        );

        return $distance <= $radius;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getVenueCoordinates(): array
    {
        return Setting::getVenueCoordinates();
    }

    public function getDetectionRadius(): int
    {
        return Setting::getVenueRadius();
    }
}
