<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'description',
        'city',
        'address',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating_avg'    => 'decimal:2',
        'ratings_count' => 'int',
    ];

    public function tables() {
        return $this->hasMany(RestaurantTable::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings()
    {
        return $this->hasMany(RestaurantRating::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')->withTimestamps();
    }
}
