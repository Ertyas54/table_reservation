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
        'password_hash',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function bookings() {
        return $this->hasMany(Booking::class);
    }
    public function isManager(): bool {
        return $this->role === 'manager';
    }
    public function getAuthPassword() {
        return $this->password_hash;
    }
}
