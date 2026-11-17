<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaggageItem extends Model
{
    protected $appends = ['heavy_cargo'];

    protected $fillable = [
        'passenger_id',
        'flight_id',
        'bag_tag_number',
        'bag_weight_kg',
    ];

    protected function casts(): array
    {
        return [
            'bag_weight_kg' => 'decimal:2',
        ];
    }

    public function getHeavyCargoAttribute(): bool
    {
        return (float) $this->bag_weight_kg > 32;
    }

    public function setBagTagNumberAttribute($value): void
    {
        $this->attributes['bag_tag_number'] = is_string($value)
            ? strtoupper(trim($value))
            : $value;
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function screeningLogs(): HasMany
    {
        return $this->hasMany(ScreeningLog::class);
    }
}