<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'venue_name',
        'venue_address',
        'venue_latitude',
        'venue_longitude',
        'venue_detection_radius',
    ];

    protected $casts = [
        'value' => 'string',
        'venue_latitude' => 'float',
        'venue_longitude' => 'float',
        'venue_detection_radius' => 'integer',
    ];

    public static function getVenueCoordinates(): array
    {
        $setting = static::first();

        return [
            'lat' => $setting?->venue_latitude ?? -6.2147245,
            'lng' => $setting?->venue_longitude ?? 106.8073332,
        ];
    }

    public static function getVenueRadius(): int
    {
        $setting = static::first();

        return $setting?->venue_detection_radius ?? 500;
    }

    public static function getVenueName(): string
    {
        $setting = static::first();

        return $setting?->venue_name ?? 'Jakarta International Expo';
    }

    public static function getVenueAddress(): string
    {
        $setting = static::first();

        return $setting?->venue_address ?? 'Jl. Expo Kemayoran, Jakarta Pusat';
    }

    public static function setVenueInfo(
        ?string $name = null,
        ?string $address = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $radius = null
    ): void {
        $setting = static::first() ?? new static;

        if ($name !== null) {
            $setting->venue_name = $name;
        }
        if ($address !== null) {
            $setting->venue_address = $address;
        }
        if ($latitude !== null) {
            $setting->venue_latitude = $latitude;
        }
        if ($longitude !== null) {
            $setting->venue_longitude = $longitude;
        }
        if ($radius !== null) {
            $setting->venue_detection_radius = $radius;
        }

        $setting->save();
    }
}
