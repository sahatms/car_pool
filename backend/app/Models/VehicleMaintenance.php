<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    protected $table = 'vehicle_maintenance';

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'start_date',
        'end_date',
        'odometer',
        'workshop_name',
        'cost',
        'status',
        'description',
        'receipt_path',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'odometer' => 'integer',
            'cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
