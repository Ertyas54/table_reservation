<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRating extends Model
{
    protected $fillable = ['restaurant_id', 'user_id', 'rating'];

    protected $casts = ['rating' => 'int'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function canUserRate(User $u, Restaurant $r): bool
    {
        return !$u->ownsRestaurant($r);
    }

    protected static function booted(): void
    {
        $refresh = function (self $m) {
            $r = Restaurant::find($m->restaurant_id);
            if ($r) {
                $r->rating_avg    = round((float) $r->ratings()->avg('rating'), 2);
                $r->ratings_count = $r->ratings()->count();
                $r->saveQuietly();
            }
        };

        static::saved($refresh);
        static::deleted($refresh);
    }
}
