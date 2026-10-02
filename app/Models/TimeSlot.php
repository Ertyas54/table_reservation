<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }
}
