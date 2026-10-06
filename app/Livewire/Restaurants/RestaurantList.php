<?php

namespace App\Livewire\Restaurants;

use App\Models\Restaurant;
use Livewire\Component;

class RestaurantList extends Component
{
    public function delete(int $restaurantId): void
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $hasActiveBookings = $restaurant->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя удалить ресторан — есть активные брони');
            return;
        }

        $restaurant->delete();
    }

    public function render()
    {
        $restaurants = Restaurant::orderBy('name')->get();

        return view('livewire.restaurants.restaurant-list', [
            'restaurants' => $restaurants,
        ]);
    }
}
