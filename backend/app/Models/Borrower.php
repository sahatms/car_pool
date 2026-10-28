<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrower extends Model
{
    protected $fillable = [
        'full_name',
        'employee_number',
        'department',
        'phone_number',
        'selfie_path',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}