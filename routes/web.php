<?php

use App\Http\Controllers\Auth\AuthController;
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
});

// Admin Route Group
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.admin');
        })->name('dashboard');
    });

// Operator Route Group (Admin is also permitted)
Route::prefix('operator')
    ->as('operator.')
    ->middleware(['auth', 'role:operator,admin'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.operator');
        })->name('dashboard');
    });

// Customer Route Group
Route::prefix('customer')
    ->as('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.customer');
        })->name('dashboard');
    });
