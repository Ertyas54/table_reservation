<?php

namespace App\Livewire\Restaurants;

use App\Models\Restaurant;
use Livewire\Component;
use App\Models\RestaurantRating;

class RestaurantList extends Component
{
    public function delete(int $restaurantId): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $restaurant = Restaurant::findOrFail($restaurantId);

        $hasActiveBookings = $restaurant->bookings()
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя удалить ресторан — есть активные брони');
            return;
        }

        $restaurant->delete();
    }

    public function rate(int $restaurantId, int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            session()->flash('error', 'Некорректная оценка');
            return;
        }

        $restaurant = Restaurant::findOrFail($restaurantId);
        $user = auth()->user();

        if (!RestaurantRating::canUserRate($user, $restaurant)) {
            session()->flash('error', 'Вы не можете оценивать свой ресторан');
            return;
        }

        RestaurantRating::updateOrCreate(
            ['user_id' => $user->id, 'restaurant_id' => $restaurant->id],
            ['rating'  => $rating]
        );

        session()->flash('message', 'Спасибо за оценку!');
    }

    public function render()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        $user = auth()->user();

        $userRatings = RestaurantRating::where('user_id', $user->id)
            ->pluck('rating', 'restaurant_id')
            ->toArray();

        $ownedRestaurantIds = $user->managedRestaurants()
            ->wherePivot('role', 'owner')
            ->pluck('restaurants.id')
            ->toArray();

        return view('livewire.restaurants.restaurant-list', [
            'restaurants'        => $restaurants,
            'userRatings'        => $userRatings,
            'ownedRestaurantIds' => $ownedRestaurantIds,
        ]);
    }
}
