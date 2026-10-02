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
        'total_price',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_price'  => 'decimal:2',
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
