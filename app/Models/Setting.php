<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public static function defined(): Collection
    {
        return collect(config('settings', []));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            'float' => (float) $this->value,
            'datetime' => $this->value ? Carbon::parse($this->value) : null,
            'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
