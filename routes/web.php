<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Restaurants\RestaurantForm;
use App\Livewire\Restaurants\RestaurantList;
use App\Livewire\Tables\TableForm;
use App\Livewire\Tables\TableList;
use App\Livewire\Users\Edit as UserEdit;
use App\Livewire\Users\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/restaurants', RestaurantList::class)->name('restaurants.index');
    Route::get('/restaurants/{restaurant}/tables', TableList::class)->name('tables.index');

    Route::middleware('admin')->group(function () {
        Route::get('/restaurants/create', RestaurantForm::class)->name('restaurants.create');
        Route::get('/restaurants/delete', RestaurantForm::class)->name('restaurants.delete');
        Route::get('/restaurants/{restaurant}/edit', RestaurantForm::class)->name('restaurants.edit');

        Route::get('/restaurants/{restaurant}/tables/create', TableForm::class)->name('tables.create');
        Route::get('/restaurants/{restaurant}/tables/{table}/edit', TableForm::class)->name('tables.edit');

        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/users/{user}/edit', UserEdit::class)->name('users.edit');
    });
});

require __DIR__.'/auth.php';
