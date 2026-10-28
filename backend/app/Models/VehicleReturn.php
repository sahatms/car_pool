<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleReturn extends Model
{
    protected $fillable = [
        'vehicle_usage_id',
        'actual_return_datetime',
        'odometer_end',
        'vehicle_condition',
        'return_notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'actual_return_datetime' => 'datetime',
            'odometer_end' => 'integer',
        ];
    }

    public function vehicleUsage(): BelongsTo
    {
        return $this->belongsTo(VehicleUsage::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
