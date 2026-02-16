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
