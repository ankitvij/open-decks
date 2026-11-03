<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Host\NightController as HostNightController;
use App\Http\Controllers\NightController;
use Illuminate\Support\Facades\Route;

// Public: DJs browse nights and book without an account.
Route::get('/', [NightController::class, 'index'])->name('nights.index');
Route::get('/nights/{night}', [NightController::class, 'show'])->name('nights.show');
Route::post('/nights/{night}/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');

// Signed links from the confirmation email.
Route::middleware('signed')->group(function () {
    Route::get('/bookings/{booking}/cancel', [BookingController::class, 'confirmCancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel.store');
});

// Host login.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Host dashboard.
Route::middleware('auth')->prefix('host')->name('host.')->group(function () {
    Route::get('/', [HostNightController::class, 'index'])->name('nights.index');
    Route::get('/nights/create', [HostNightController::class, 'create'])->name('nights.create');
    Route::post('/nights', [HostNightController::class, 'store'])->name('nights.store');
    Route::get('/nights/{night}', [HostNightController::class, 'show'])->name('nights.show');
    Route::delete('/nights/{night}/bookings/{booking}', [HostNightController::class, 'removeBooking'])
        ->name('nights.bookings.destroy');
});
