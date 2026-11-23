<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isOwner(): bool   { return $this->role === 'owner'; }
    public function isManager(): bool { return $this->role === 'manager'; }

    public function managedRestaurants()
    {
        return $this->belongsToMany(Restaurant::class)
            ->withPivot('role')->withTimestamps();
    }

    public function ownsRestaurant(Restaurant $r): bool
    {
        return $this->isOwner()
            && $this->managedRestaurants()
                ->wherePivot('role', 'owner')
                ->whereKey($r->id)->exists();
    }

    public function managesRestaurant(Restaurant $r): bool
    {
        return $this->isManager()
            && $this->managedRestaurants()
                ->wherePivot('role', 'manager')
                ->whereKey($r->id)->exists();
    }

    public function canManageRestaurant(Restaurant $r): bool
    {
        return $this->isAdmin() || $this->ownsRestaurant($r) || $this->managesRestaurant($r);
    }

    public function canCreateTable(Restaurant $r): bool
    {
        return $this->isAdmin() || $this->ownsRestaurant($r);
    }
}
