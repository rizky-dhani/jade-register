<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'affiliation',
        'profession',
        'preferred_visit_date',
        'marketing_source',
    ];

    protected $casts = [
        'preferred_visit_date' => 'date',
    ];

    public function getFormattedVisitDateAttribute(): string
    {
        return $this->preferred_visit_date->format('l, j F Y');
    }
}
