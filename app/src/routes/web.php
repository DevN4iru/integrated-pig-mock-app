<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmSettingController;
use App\Http\Controllers\FarmSummaryReportController;
use App\Http\Controllers\FeedLogController;
use App\Http\Controllers\HealthLogController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenController;
use App\Http\Controllers\PigController;
use App\Http\Controllers\PigTransferController;
use App\Http\Controllers\ProtocolExecutionController;
use App\Http\Controllers\ProtocolProgramController;
use App\Http\Controllers\ReproductionCycleController;
use App\Http\Controllers\ReproductionCycleUpdateController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\VaccinationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Pigstep Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/reports/farm-summary.csv', [FarmSummaryReportController::class, 'csv'])
        ->name('reports.farm-summary.csv');

    Route::get('/reports/farm-summary.pdf', [FarmSummaryReportController::class, 'pdf'])
        ->name('reports.farm-summary.pdf');

    Route::post('/reports/farm-summary/email', [FarmSummaryReportController::class, 'email'])
        ->name('reports.farm-summary.email');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');

    /*
    |--------------------------------------------------------------------------
    | Farm Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings/farm', [FarmSettingController::class, 'edit'])->name('settings.farm.edit');
    Route::put('/settings/farm', [FarmSettingController::class, 'update'])->name('settings.farm.update');

    /*
    |--------------------------------------------------------------------------
    | Protocol Programs
    |--------------------------------------------------------------------------
    */

    Route::get('/protocol-programs', [ProtocolProgramController::class, 'index'])->name('protocol-programs.index');
    Route::get('/protocol-programs/{protocolTemplate}/edit', [ProtocolProgramController::class, 'edit'])->name('protocol-programs.edit');
    Route::put('/protocol-programs/{protocolTemplate}', [ProtocolProgramController::class, 'update'])->name('protocol-programs.update');
    Route::get('/protocol-programs/{protocolTemplate}', [ProtocolProgramController::class, 'show'])->name('protocol-programs.show');

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
    Route::delete('/pigs/{pig}/remove-records', [PigController::class, 'removeFromRecords'])->name('pigs.remove-records');

    /*
    |--------------------------------------------------------------------------
    | Reproduction / Breeding
    |--------------------------------------------------------------------------
    */

    Route::get('/breeding', [ReproductionCycleController::class, 'index'])->name('reproduction-cycles.index');
    Route::get('/pigs/{pig}/reproduction-cycles/create', [ReproductionCycleController::class, 'create'])->name('reproduction-cycles.create');
    Route::post('/pigs/{pig}/reproduction-cycles', [ReproductionCycleController::class, 'store'])->name('reproduction-cycles.store');

    Route::get('/reproduction-cycles/{reproductionCycle}', [ReproductionCycleController::class, 'show'])->name('reproduction-cycles.show');
    Route::post('/reproduction-cycles/{reproductionCycle}/updates', [ReproductionCycleUpdateController::class, 'store'])->name('reproduction-cycle-updates.store');
    Route::get('/reproduction-cycles/{reproductionCycle}/attempts/create', [ReproductionCycleController::class, 'createNextAttempt'])->name('reproduction-cycles.attempts.create');
    Route::post('/reproduction-cycles/{reproductionCycle}/attempts', [ReproductionCycleController::class, 'storeNextAttempt'])->name('reproduction-cycles.attempts.store');

    Route::get('/reproduction-cycles/{reproductionCycle}/born-piglets/create', [PigController::class, 'createBornBatch'])->name('pigs.create-born-batch');
    Route::post('/reproduction-cycles/{reproductionCycle}/born-piglets', [PigController::class, 'storeBornBatch'])->name('pigs.store-born-batch');

    Route::get('/reproduction-cycles/{reproductionCycle}/edit', [ReproductionCycleController::class, 'edit'])->name('reproduction-cycles.edit');
    Route::put('/reproduction-cycles/{reproductionCycle}', [ReproductionCycleController::class, 'update'])->name('reproduction-cycles.update');
    Route::delete('/reproduction-cycles/{reproductionCycle}', [ReproductionCycleController::class, 'destroy'])->name('reproduction-cycles.destroy');

    /*
    |--------------------------------------------------------------------------
    | Pig Transfers
    |--------------------------------------------------------------------------
    */

    Route::get('/pigs/{pig}/transfers/create', [PigTransferController::class, 'create'])->name('pig-transfers.create');
    Route::post('/pigs/{pig}/transfers', [PigTransferController::class, 'store'])->name('pig-transfers.store');
    Route::post('/pig-transfers/batch', [PigTransferController::class, 'batchStore'])->name('pig-transfers.batch');

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
    | Protocol Executions
    |--------------------------------------------------------------------------
    */

    Route::post('/pigs/{pig}/protocol-executions', [ProtocolExecutionController::class, 'upsert'])
        ->name('protocol-executions.upsert');

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
    Route::post('/sales/batch', [SaleController::class, 'batchStore'])->name('sales.batch');
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
    Route::get('/pens/{pen}', [PenController::class, 'show'])->name('pens.show');

    Route::get('/pens/{pen}/edit', [PenController::class, 'edit'])->name('pens.edit');
    Route::put('/pens/{pen}', [PenController::class, 'update'])->name('pens.update');
    Route::delete('/pens/{pen}', [PenController::class, 'destroy'])->name('pens.destroy');
});
