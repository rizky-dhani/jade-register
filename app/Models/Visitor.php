<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'affiliation',
        'qr_token',
        'barcode',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function isScanned(): bool
    {
        return $this->scanned_at !== null;
    }

    public function markAsScanned(): void
    {
        $this->update(['scanned_at' => now()]);
    }

    public function visitorAttendances(): HasMany
    {
        return $this->hasMany(VisitorAttendance::class);
    }
}
