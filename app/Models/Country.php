<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_indonesia',
        'phone_code',
    ];

    protected $casts = [
        'is_indonesia' => 'boolean',
    ];

    public function seminarRegistrations(): HasMany
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function scopeIndonesia($query)
    {
        return $query->where('is_indonesia', true);
    }

    public function scopeInternational($query)
    {
        return $query->where('is_indonesia', false);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }
}
