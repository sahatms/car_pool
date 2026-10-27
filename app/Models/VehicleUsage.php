<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleUsage extends Model
{
    protected $fillable = [
        'booking_id',
        'actual_start_datetime',
        'odometer_start',
        'usage_notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'actual_start_datetime' => 'datetime',
            'odometer_start' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function vehicleReturn(): HasOne
    {
        return $this->hasOne(VehicleReturn::class);
    }
}
