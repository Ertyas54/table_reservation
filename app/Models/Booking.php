<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'table_id',
        'slot_id',
        'booking_date',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}
