<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('auth')->group(function () {
    Route::get('/me', function () {
        return redirect()->route('profile');
    })->name('me');

    Route::get('/fokus', function () {
        return redirect()->route('pomodoro');
    })->name('fokus');

    Route::get('/catatan', function () {
        return view('catatan');
    })->name('catatan');

    Route::get('/belajar', function () {
        return view('belajar');
    })->name('belajar');

    Route::get('/pomodoro', function () {
        return view('pomodoro');
    })->name('pomodoro');

    Route::get('/utama', function () {
        return view('utama');
    })->name('utama');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

Route::middleware(['auth', 'admin.validate'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', [AdminController::class, 'usersPage'])->name('admin.users');

    Route::get('/admin/pomodoro', function () {
        return view('admin.pomodoro');
    })->name('admin.pomodoro');

    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});
