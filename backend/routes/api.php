
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingApprovalController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/bookings', [BookingController::class, 'store']);

Route::get('/vehicles', [VehicleController::class, 'index']);

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

    Route::get(
        '/bookings/{id}/pdf',
        [BookingController::class, 'downloadPdf']
    );
});
