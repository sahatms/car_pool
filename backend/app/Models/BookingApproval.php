<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingApproval extends Model
{
    protected $fillable = [
        'booking_id',
        'admin_id',
        'director_decision',
        'director_decision_date',
        'admin_decision',
        'admin_decision_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'director_decision_date' => 'datetime',
            'admin_decision_date' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
