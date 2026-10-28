<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_code',
        'borrower_id',
        'vehicle_id',
        'start_datetime',
        'end_datetime',
        'destination',
        'purpose',
        'status',
        'admin_notes',
        'document_printed_at',
        'processed_by',
        'passenger_count',
        'driver_name',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'document_printed_at' => 'datetime',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approval(): HasOne
    {
        return $this->hasOne(BookingApproval::class);
    }

    public function vehicleUsage(): HasOne
    {
        return $this->hasOne(VehicleUsage::class);
    }
}