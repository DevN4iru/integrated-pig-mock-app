<?php

use App\Http\Controllers\PenController;
use App\Http\Controllers\PigController;
use App\Http\Controllers\HealthLogController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\FeedLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::get('/pigs', [PigController::class, 'index'])->name('pigs.index');
Route::get('/pigs/create', [PigController::class, 'create'])->name('pigs.create');
Route::post('/pigs', [PigController::class, 'store'])->name('pigs.store');

Route::get('/pigs/{pig}', [PigController::class, 'show'])->name('pigs.show');
Route::get('/pigs/{pig}/edit', [PigController::class, 'edit'])->name('pigs.edit');
Route::put('/pigs/{pig}', [PigController::class, 'update'])->name('pigs.update');
Route::delete('/pigs/{pig}', [PigController::class, 'destroy'])->name('pigs.destroy');

Route::get('/pigs/{pig}/health-logs/create', [HealthLogController::class, 'create'])->name('health-logs.create');
Route::post('/pigs/{pig}/health-logs', [HealthLogController::class, 'store'])->name('health-logs.store');

Route::get('/pigs/{pig}/medications/create', [MedicationController::class, 'create'])->name('medications.create');
Route::post('/pigs/{pig}/medications', [MedicationController::class, 'store'])->name('medications.store');

Route::get('/pigs/{pig}/vaccinations/create', [VaccinationController::class, 'create'])->name('vaccinations.create');
Route::post('/pigs/{pig}/vaccinations', [VaccinationController::class, 'store'])->name('vaccinations.store');

Route::get('/pigs/{pig}/mortality/create', [MortalityLogController::class, 'create'])->name('mortality.create');
Route::post('/pigs/{pig}/mortality', [MortalityLogController::class, 'store'])->name('mortality.store');

Route::get('/pigs/{pig}/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/pigs/{pig}/sales', [SaleController::class, 'store'])->name('sales.store');

Route::get('/pigs/{pig}/feed-logs/create', [FeedLogController::class, 'create'])->name('feed-logs.create');
Route::post('/pigs/{pig}/feed-logs', [FeedLogController::class, 'store'])->name('feed-logs.store');

Route::get('/pens', [PenController::class, 'index'])->name('pens.index');
Route::get('/pens/create', [PenController::class, 'create'])->name('pens.create');
Route::post('/pens', [PenController::class, 'store'])->name('pens.store');

Route::get('/pens/{pen}/edit', [PenController::class, 'edit'])->name('pens.edit');
Route::put('/pens/{pen}', [PenController::class, 'update'])->name('pens.update');
Route::delete('/pens/{pen}', [PenController::class, 'destroy'])->name('pens.destroy');