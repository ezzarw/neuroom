<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('landing');
})->name('landing');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Redirect helper
    Route::get('/me', fn() => redirect()->route('profile'))->name('me');
    Route::get('/fokus', fn() => redirect()->route('pomodoro'))->name('fokus');

    // Halaman utama user
    Route::view('/belajar', 'belajar')->name('belajar');
    Route::view('/catatan', 'catatan')->name('catatan');
    Route::view('/pomodoro', 'pomodoro')->name('pomodoro');
    Route::view('/utama', 'utama')->name('utama');
    Route::view('/profile', 'profile')->name('profile');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.validate'])->group(function () {

    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

    Route::view('/admin/users', 'admin.users')->name('admin.users');

    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))
        ->name('dashboard');
});