<?php

namespace App\Livewire\Restaurants;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RestaurantForm extends Component
{
    public ?Restaurant $restaurant = null;

    public string $name = '';
    public string $description = '';
    public string $city = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $opening_time = '10:00';
    public string $closing_time = '23:00';
    public bool $is_active = true;

    public function mount(?Restaurant $restaurant = null): void
    {
        if ($restaurant && $restaurant->exists) {
            $this->restaurant = $restaurant;
            $this->name = $restaurant->name;
            $this->description = $restaurant->description ?? '';
            $this->city = $restaurant->city;
            $this->address = $restaurant->address;
            $this->phone = $restaurant->phone ?? '';
            $this->email = $restaurant->email ?? '';
            $this->opening_time = substr($restaurant->opening_time, 0, 5);
            $this->closing_time = substr($restaurant->closing_time, 0, 5);
            $this->is_active = $restaurant->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('restaurants', 'name')->ignore($this->restaurant?->id),
            ],
            'description' => 'nullable|string',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->restaurant) {
            $this->restaurant->update($validated);
        } else {
            Restaurant::create($validated);
        }

        $this->redirectRoute('restaurants.index');
    }

    public function render()
    {
        return view('livewire.restaurants.restaurant-form');
    }
}
