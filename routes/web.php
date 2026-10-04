<?php

use App\Livewire\Tables\TableForm;
use App\Livewire\Tables\TableList;
use Illuminate\Support\Facades\Route;

Route::get('/restaurants/{restaurant}/tables', TableList::class)->name('tables.index');
Route::get('/restaurants/{restaurant}/tables/create', TableForm::class)->name('tables.create');
Route::get('/restaurants/{restaurant}/tables/{table}/edit', TableForm::class)->name('tables.edit');
