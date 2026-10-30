
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingApprovalController;
use App\Http\Controllers\Api\VehicleTaxRecordController;
use App\Http\Controllers\Api\VehicleMaintenanceController;
use App\Http\Controllers\Api\VehicleUsageController;
use App\Http\Controllers\Api\VehicleReturnController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/bookings', [BookingController::class, 'store']);

Route::get('/vehicles', [VehicleController::class, 'index']);

Route::get(
    '/bookings/{id}/pdf/{token}',
    [BookingController::class, 'downloadPdf']
)->name('bookings.pdf');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('vehicles', VehicleController::class)
        ->except(['index']);

    Route::get('/borrowers', [BorrowerController::class, 'index']);
    Route::get('/borrowers/{id}', [BorrowerController::class, 'show']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);

    Route::patch(
        '/bookings/{id}/status',
        [BookingController::class, 'updateStatus']
    );

    Route::apiResource(
        'vehicle-tax-records',
        VehicleTaxRecordController::class
    );

    Route::apiResource(
        'vehicle-maintenances',
        VehicleMaintenanceController::class
    );

    Route::post(
        '/bookings/{id}/usage',
        [VehicleUsageController::class, 'store']
    );

    Route::post(
        '/vehicle-usages/{id}/return',
        [VehicleReturnController::class, 'store']
    );
});
