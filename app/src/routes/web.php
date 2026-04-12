<?php

use App\Http\Controllers\PenController;
use App\Http\Controllers\PigController;
use App\Http\Controllers\HealthLogController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\FeedLogController;
use App\Http\Controllers\FarmSettingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Farm Settings
|--------------------------------------------------------------------------
*/

Route::get('/settings/farm', [FarmSettingController::class, 'edit'])->name('settings.farm.edit');
Route::put('/settings/farm', [FarmSettingController::class, 'update'])->name('settings.farm.update');

/*
|--------------------------------------------------------------------------
| Pigs
|--------------------------------------------------------------------------
*/

Route::get('/pigs', [PigController::class, 'index'])->name('pigs.index');
Route::get('/pigs/create', [PigController::class, 'create'])->name('pigs.create');
Route::post('/pigs', [PigController::class, 'store'])->name('pigs.store');

Route::get('/pigs/{pig}', [PigController::class, 'show'])->name('pigs.show');
Route::get('/pigs/{pig}/edit', [PigController::class, 'edit'])->name('pigs.edit');
Route::put('/pigs/{pig}', [PigController::class, 'update'])->name('pigs.update');
Route::delete('/pigs/{pig}', [PigController::class, 'destroy'])->name('pigs.destroy');
Route::post('/pigs/{pig}/restore', [PigController::class, 'restore'])->name('pigs.restore');
Route::delete('/pigs/{pig}/force-delete', [PigController::class, 'forceDelete'])->name('pigs.force-delete');

/*
|--------------------------------------------------------------------------
| Health Logs
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/health-logs/create', [HealthLogController::class, 'create'])->name('health-logs.create');
Route::post('/pigs/{pig}/health-logs', [HealthLogController::class, 'store'])->name('health-logs.store');
Route::get('/pigs/{pig}/health-logs/{healthLog}/edit', [HealthLogController::class, 'edit'])->name('health-logs.edit');
Route::put('/pigs/{pig}/health-logs/{healthLog}', [HealthLogController::class, 'update'])->name('health-logs.update');
Route::delete('/pigs/{pig}/health-logs/{healthLog}', [HealthLogController::class, 'destroy'])->name('health-logs.destroy');

/*
|--------------------------------------------------------------------------
| Medication
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/medications/create', [MedicationController::class, 'create'])->name('medications.create');
Route::post('/pigs/{pig}/medications', [MedicationController::class, 'store'])->name('medications.store');
Route::get('/pigs/{pig}/medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
Route::put('/pigs/{pig}/medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
Route::delete('/pigs/{pig}/medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');

/*
|--------------------------------------------------------------------------
| Vaccination
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/vaccinations/create', [VaccinationController::class, 'create'])->name('vaccinations.create');
Route::post('/pigs/{pig}/vaccinations', [VaccinationController::class, 'store'])->name('vaccinations.store');
Route::get('/pigs/{pig}/vaccinations/{vaccination}/edit', [VaccinationController::class, 'edit'])->name('vaccinations.edit');
Route::put('/pigs/{pig}/vaccinations/{vaccination}', [VaccinationController::class, 'update'])->name('vaccinations.update');
Route::delete('/pigs/{pig}/vaccinations/{vaccination}', [VaccinationController::class, 'destroy'])->name('vaccinations.destroy');

/*
|--------------------------------------------------------------------------
| Mortality
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/mortality/create', [MortalityLogController::class, 'create'])->name('mortality.create');
Route::post('/pigs/{pig}/mortality', [MortalityLogController::class, 'store'])->name('mortality.store');
Route::get('/pigs/{pig}/mortality/{mortalityLog}/edit', [MortalityLogController::class, 'edit'])->name('mortality.edit');
Route::put('/pigs/{pig}/mortality/{mortalityLog}', [MortalityLogController::class, 'update'])->name('mortality.update');
Route::delete('/pigs/{pig}/mortality/{mortalityLog}', [MortalityLogController::class, 'destroy'])->name('mortality.destroy');

/*
|--------------------------------------------------------------------------
| Sales
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/pigs/{pig}/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/pigs/{pig}/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
Route::put('/pigs/{pig}/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
Route::delete('/pigs/{pig}/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

/*
|--------------------------------------------------------------------------
| Feed Logs
|--------------------------------------------------------------------------
*/

Route::get('/pigs/{pig}/feed-logs/create', [FeedLogController::class, 'create'])->name('feed-logs.create');
Route::post('/pigs/{pig}/feed-logs', [FeedLogController::class, 'store'])->name('feed-logs.store');
Route::get('/pigs/{pig}/feed-logs/{feedLog}/edit', [FeedLogController::class, 'edit'])->name('feed-logs.edit');
Route::put('/pigs/{pig}/feed-logs/{feedLog}', [FeedLogController::class, 'update'])->name('feed-logs.update');
Route::delete('/pigs/{pig}/feed-logs/{feedLog}', [FeedLogController::class, 'destroy'])->name('feed-logs.destroy');

/*
|--------------------------------------------------------------------------
| Pens
|--------------------------------------------------------------------------
*/

Route::get('/pens', [PenController::class, 'index'])->name('pens.index');
Route::get('/pens/create', [PenController::class, 'create'])->name('pens.create');
Route::post('/pens', [PenController::class, 'store'])->name('pens.store');

Route::get('/pens/{pen}/edit', [PenController::class, 'edit'])->name('pens.edit');
Route::put('/pens/{pen}', [PenController::class, 'update'])->name('pens.update');
Route::delete('/pens/{pen}', [PenController::class, 'destroy'])->name('pens.destroy');
