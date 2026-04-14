<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'currency',
        'max_seats',
        'is_active',
        'available_from',
        'available_until',
        'sort_order',
        'disable_condition',
        'disable_conditions',
    ];

    protected $casts = [
        'price' => 'integer',
        'max_seats' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
        'disable_condition' => 'string',
        'disable_conditions' => 'array',
    ];

    public function addonRegistrations(): HasMany
    {
        return $this->hasMany(AddonRegistration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('available_from')
                ->orWhere('available_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('available_until')
                ->orWhere('available_until', '>=', now());
        });
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency === 'USD') {
            return '$'.number_format($this->price, 2);
        }

        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function getRemainingStockAttribute(): int
    {
        if ($this->max_seats === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_seats - $this->addonRegistrations()
            ->where('payment_status', 'verified')
            ->count());
    }

    public function isFull(): bool
    {
        return $this->remaining_stock <= 0;
    }

    public function isDisabled(array $context = []): bool
    {
        if (! $this->is_active) {
            return true;
        }

        // Evaluate new disable_conditions if available
        if (! empty($this->disable_conditions)) {
            foreach ($this->disable_conditions as $condition) {
                if ($this->evaluateCondition($condition, $context)) {
                    return true;
                }
            }
        }

        // Fallback to legacy disable_condition
        return match ($this->disable_condition) {
            'never' => false,
            'when_full' => $this->isFull(),
            'when_date_passed' => $this->available_until && $this->available_until->isPast(),
            'always' => true,
            default => false,
        };
    }

    protected function evaluateCondition(array $condition, array $context): bool
    {
        $model = $condition['model'] ?? null;
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if ($model === null || $field === null) {
            return false;
        }

        $modelValue = $this->resolveModelValue($model, $field, $context);

        return $this->compareValues($modelValue, $value, $operator);
    }

    protected function resolveModelValue(string $model, string $field, array $context): mixed
    {
        return match ($model) {
            'seminar' => $context['seminar']->{$field} ?? null,
            'addon' => $this->{$field},
            'seminar_registration' => $context['registration']->{$field} ?? null,
            default => null,
        };
    }

    protected function compareValues(mixed $actual, mixed $expected, string $operator): bool
    {
        // Handle null comparisons
        if ($operator === 'is_null') {
            return $actual === null;
        }

        if ($operator === 'is_not_null') {
            return $actual !== null;
        }

        // Cast booleans for comparison
        if (is_bool($expected) || in_array($expected, ['true', 'false'], true)) {
            $expected = filter_var($expected, FILTER_VALIDATE_BOOLEAN);
            $actual = filter_var($actual, FILTER_VALIDATE_BOOLEAN);
        }

        // Cast numbers for comparison if both are numeric
        if (is_numeric($actual) && is_numeric($expected)) {
            $actual = (float) $actual;
            $expected = (float) $expected;
        }

        return match ($operator) {
            '=' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '<' => $actual < $expected,
            '>=' => $actual >= $expected,
            '<=' => $actual <= $expected,
            'contains' => is_string($actual) && str_contains($actual, (string) $expected),
            'not_contains' => is_string($actual) && ! str_contains($actual, (string) $expected),
            default => false,
        };
    }
}
