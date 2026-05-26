<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::patch('/me', [UserController::class, 'updateMe']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/summary', [SummaryController::class, 'summary']);
        Route::get('/pomodoro/history', [PomodoroController::class, 'history']);
        Route::post('/pomodoro/history', [PomodoroController::class, 'store']);
        

        Route::get('/notes', [NoteController::class, 'index']);
        Route::get('/notes/{id}', [NoteController::class, 'show']);
        Route::post('/notes', [NoteController::class, 'store']);
        Route::patch('/notes/{id}', [NoteController::class, 'update']);
        Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'admin.validate'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'index']);
        Route::post('/users', [AdminController::class, 'store']);
        Route::put('/users/{user}', [AdminController::class, 'update']);
        Route::delete('/users/{user}', [AdminController::class, 'destroy']);
        Route::get('/pomodoro', [AdminController::class, 'pomodoroSessions']);
    });
});
