<?php

use App\Http\Controllers\PenController;
use App\Http\Controllers\PigController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::get('/pigs', [PigController::class, 'index'])->name('pigs.index');
Route::get('/pigs/create', [PigController::class, 'create'])->name('pigs.create');
Route::post('/pigs', [PigController::class, 'store'])->name('pigs.store');

Route::get('/pens', [PenController::class, 'index'])->name('pens.index');
Route::get('/pens/create', [PenController::class, 'create'])->name('pens.create');
Route::post('/pens', [PenController::class, 'store'])->name('pens.store');