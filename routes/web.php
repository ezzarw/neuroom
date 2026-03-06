<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});
Route::get('/belajar', function () {
    return view('belajar');
});
Route::get('/pomodoro', function () {
    return view('pomodoro');
});

Route::get('/auth-test', function () {
    return view('auth-test');
});
Route::get('/utama', function () {
    return view('utama');
})->name('utama');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/users', [AdminController::class, 'usersPage'])->name('admin.users');

Route::get('/admin/pomodoro', function () {
    return view('admin.pomodoro');
})->name('admin.pomodoro');
