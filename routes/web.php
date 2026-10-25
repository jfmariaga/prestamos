<?php

use App\Models\Loan;
use App\Models\PersonalLoan;
use Illuminate\Http\Request;
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
    Route::get('loans/{loan}/edit', function (Loan $loan) {
        if ($loan->payments()->exists()) {
            session()->flash('error', 'No se puede editar un préstamo que ya tiene abonos registrados.');

            return redirect()->route('loans.show', $loan);
        }

        return view('loans.edit', ['loan' => $loan]);
    })->name('loans.edit');
    Route::get('loans/{loan}', function (Loan $loan) {
        return view('loans.show', ['loan' => $loan]);
    })->name('loans.show');

    Route::view('users', 'users.index')->name('users.index');
    Route::view('users/create', 'users.create')->name('users.create');

    Route::get('reportes/interes-cobrado', function (Request $request) {
        return view('reports.interest-collected', [
            'start' => $request->query('start', now()->startOfMonth()->toDateString()),
            'end' => $request->query('end', now()->endOfMonth()->toDateString()),
        ]);
    })->name('reports.interest-collected');

    Route::get('reportes/interes-por-cobrar', function (Request $request) {
        return view('reports.interest-due', [
            'start' => $request->query('start', now()->startOfMonth()->toDateString()),
            'end' => $request->query('end', now()->endOfMonth()->toDateString()),
        ]);
    })->name('reports.interest-due');

    Route::view('reportes/mora', 'reports.overdue')->name('reports.overdue');

    Route::view('prestamos-personales', 'personal-loans.index')->name('personal-loans.index');
    Route::view('prestamos-personales/crear', 'personal-loans.create')->name('personal-loans.create');
    Route::get('prestamos-personales/{personalLoan}/editar', function (PersonalLoan $personalLoan) {
        abort_unless($personalLoan->user_id === auth()->id(), 403);

        return view('personal-loans.edit', ['personalLoan' => $personalLoan]);
    })->name('personal-loans.edit');
});

require __DIR__.'/auth.php';
