<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Web\ShipmentExceptionWebController;
use App\Http\Controllers\Web\ShipmentTrackingWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Landing Page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Session & Smart Dashboard Redirection
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            default => redirect()->route('customer.dashboard'),
        };
    })->name('dashboard');

    // Visual Interactive Timeline & Tracking Checkpoints
    Route::get('/shipments/{shipment}/tracking', [ShipmentTrackingWebController::class, 'show'])->name('shipments.tracking.show');
    Route::post('/shipments/{shipment}/tracking/checkpoints', [ShipmentTrackingWebController::class, 'addCheckpoint'])->name('shipments.tracking.checkpoint.store');

    // Exception Center & Disruption Management (Operator & Admin)
    Route::middleware('role:operator,admin')->group(function () {
        Route::get('/exceptions', [ShipmentExceptionWebController::class, 'index'])->name('exceptions.index');
        Route::post('/shipments/{shipment}/exceptions', [ShipmentExceptionWebController::class, 'store'])->name('shipments.exceptions.store');
        Route::post('/exceptions/{exception}/resolve', [ShipmentExceptionWebController::class, 'resolve'])->name('exceptions.resolve');
    });
});

// Admin Route Group
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.admin');
        })->name('dashboard');

        Route::get('/shipments', function () {
            return view('dashboard.shipments', [
                'shipments' => \App\Models\Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items'])
                    ->latest()
                    ->paginate(10),
            ]);
        })->name('shipments');

        Route::get('/carriers', function () {
            return view('dashboard.carriers', [
                'carriers' => \App\Models\Carrier::orderBy('name')->get(),
            ]);
        })->name('carriers');

        Route::get('/locations', function () {
            return view('dashboard.locations', [
                'locations' => \App\Models\Location::orderBy('name')->get(),
            ]);
        })->name('locations');
    });

// Operator Route Group (Admin is also permitted)
Route::prefix('operator')
    ->as('operator.')
    ->middleware(['auth', 'role:operator,admin'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.operator');
        })->name('dashboard');

        Route::get('/shipments', function () {
            return view('dashboard.shipments', [
                'shipments' => \App\Models\Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items'])
                    ->latest()
                    ->paginate(10),
            ]);
        })->name('shipments');

        Route::get('/carriers', function () {
            return view('dashboard.carriers', [
                'carriers' => \App\Models\Carrier::orderBy('name')->get(),
            ]);
        })->name('carriers');

        Route::get('/locations', function () {
            return view('dashboard.locations', [
                'locations' => \App\Models\Location::orderBy('name')->get(),
            ]);
        })->name('locations');
    });

// Customer Route Group
Route::prefix('customer')
    ->as('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.customer');
        })->name('dashboard');

        Route::get('/shipments', function () {
            return view('dashboard.shipments', [
                'shipments' => auth()->user()
                    ->shipments()
                    ->with(['carrier', 'origin', 'destination', 'items'])
                    ->latest()
                    ->paginate(10),
            ]);
        })->name('shipments');
    });
