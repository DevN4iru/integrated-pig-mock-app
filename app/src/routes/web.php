<?php

use App\Http\Controllers\PenController;
use App\Http\Controllers\PigController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::get('/pigs', [PigController::class, 'index'])->name('pigs.index');
Route::get('/pigs/create', [PigController::class, 'create'])->name('pigs.create');
Route::post('/pigs', [PigController::class, 'store'])->name('pigs.store');

Route::get('/pigs/{pig}/edit', [PigController::class, 'edit'])->name('pigs.edit');
Route::put('/pigs/{pig}', [PigController::class, 'update'])->name('pigs.update');
Route::delete('/pigs/{pig}', [PigController::class, 'destroy'])->name('pigs.destroy');

Route::get('/pens', [PenController::class, 'index'])->name('pens.index');
Route::get('/pens/create', [PenController::class, 'create'])->name('pens.create');
Route::post('/pens', [PenController::class, 'store'])->name('pens.store');

Route::get('/pens/{pen}/edit', [PenController::class, 'edit'])->name('pens.edit');
Route::put('/pens/{pen}', [PenController::class, 'update'])->name('pens.update');
Route::delete('/pens/{pen}', [PenController::class, 'destroy'])->name('pens.destroy');