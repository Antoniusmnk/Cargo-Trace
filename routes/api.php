<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\ExceptionApiController;
use App\Http\Controllers\Api\LocationApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public Authentication Endpoints
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected Endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);

    // Master Data: Carriers
    Route::get('/carriers', [CarrierApiController::class, 'index']);
    Route::post('/carriers', [CarrierApiController::class, 'store']);

    // Master Data: Locations
    Route::get('/locations', [LocationApiController::class, 'index']);
    Route::post('/locations', [LocationApiController::class, 'store']);

    // Shipments Resource
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::post('/shipments', [ShipmentApiController::class, 'store']);
    Route::get('/shipments/{id}', [ShipmentApiController::class, 'show']);
    Route::put('/shipments/{id}', [ShipmentApiController::class, 'update']);
    Route::patch('/shipments/{id}', [ShipmentApiController::class, 'update']);
    Route::delete('/shipments/{id}', [ShipmentApiController::class, 'destroy']);

    // Tracking & Milestone Events
    Route::get('/shipments/{shipment}/tracking', [TrackingApiController::class, 'index'])->name('api.shipments.tracking.index');
    Route::post('/shipments/{shipment}/tracking', [TrackingApiController::class, 'store'])->name('api.shipments.tracking.store');

    // Exceptions & Delay Management
    Route::get('/shipments/{shipment}/exceptions', [ExceptionApiController::class, 'indexForShipment'])->name('api.shipments.exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionApiController::class, 'store'])->name('api.shipments.exceptions.store');
    Route::get('/exceptions', [ExceptionApiController::class, 'indexAll'])->name('api.exceptions.index');
    Route::patch('/exceptions/{exception}', [ExceptionApiController::class, 'update'])->name('api.exceptions.update');
    Route::post('/shipments/{shipment}/recalculate-eta', [ExceptionApiController::class, 'recalculateEta'])->name('api.shipments.recalculate-eta');
});
