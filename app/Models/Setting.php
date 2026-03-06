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
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getVenueCoordinates(): array
    {
        return [
            'lat' => (float) static::get('venue_latitude', -6.2147245),
            'lng' => (float) static::get('venue_longitude', 106.8073332),
        ];
    }

    public static function getVenueRadius(): int
    {
        return (int) static::get('venue_detection_radius', 500);
    }
}
