<?php

namespace App\Livewire\Tables;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Livewire\Component;
use Illuminate\Validation\Rule;

class TableForm extends Component
{
    public Restaurant $restaurant;
    public ?RestaurantTable $table = null;

    public string $table_number = '';
    public string $location = 'Основной зал';
    public bool $is_active = true;

    public function mount(Restaurant $restaurant, ?RestaurantTable $table = null): void
    {
        $this->restaurant = $restaurant;

        if ($table) {
            $this->table = $table;
            $this->table_number = $table->table_number;
            $this->location = $table->location;
            $this->is_active = $table->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'table_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('restaurant_tables', 'table_number')
                    ->where('restaurant_id', $this->restaurant->id)
                    ->ignore($this->table?->id),
            ],
            'location' => 'required|in:Основной зал,VIP,Терраса,Бар',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->table) {
            $this->table->update($validated);
        } else {
            $this->restaurant->tables()->create($validated);
        }

        $this->redirectRoute('tables.index', ['restaurant' => $this->restaurant->id]);
    }

    public function render()
    {
        return view('livewire.tables.table-form');
    }
}
