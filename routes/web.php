<?php

use App\Livewire\Restaurants\RestaurantForm;
use App\Livewire\Restaurants\RestaurantList;
use App\Livewire\Tables\TableForm;
use App\Livewire\Tables\TableList;
use Illuminate\Support\Facades\Route;

Route::get('/restaurants', RestaurantList::class)->name('restaurants.index');
Route::get('/restaurants/create', RestaurantForm::class)->name('restaurants.create');
Route::get('/restaurants/{restaurant}/edit', RestaurantForm::class)->name('restaurants.edit');

Route::get('/restaurants/{restaurant}/tables', TableList::class)->name('tables.index');
Route::get('/restaurants/{restaurant}/tables/create', TableForm::class)->name('tables.create');
Route::get('/restaurants/{restaurant}/tables/{table}/edit', TableForm::class)->name('tables.edit');
