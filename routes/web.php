<?php

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
Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/users', function () {
    return view('admin.users');
})->name('admin.users');

Route::get('/admin/pomodoro', function () {
    return view('admin.pomodoro');
})->name('admin.pomodoro');