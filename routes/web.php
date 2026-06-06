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

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.validate'])->group(function () {

    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

    Route::get('/admin/users', [AdminController::class, 'usersPage'])
        ->name('admin.users');

    Route::view('/admin/pomodoro', 'admin.pomodoro')->name('admin.pomodoro');

    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))
        ->name('dashboard');
});