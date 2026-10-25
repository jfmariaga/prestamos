<?php

use App\Models\Loan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('loans/create', 'loans.create')->name('loans.create');
    Route::get('loans/{loan}', function (Loan $loan) {
        return view('loans.show', ['loan' => $loan]);
    })->name('loans.show');

    Route::view('users', 'users.index')->name('users.index');
    Route::view('users/create', 'users.create')->name('users.create');
});

require __DIR__.'/auth.php';
