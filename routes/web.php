<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SummaryController;
use Illuminate\Support\Facades\Route;

// admin page
Route::middleware(['auth', 'admin.validate'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', [AdminController::class, 'usersPage'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'createUserWeb'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUserWeb'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUserWeb'])->name('admin.users.delete');

    Route::get('/admin/pomodoro', function () {
        return view('admin.pomodoro');
    })->name('admin.pomodoro');
});
// admin page end

Route::get('/', function () {
    return view('landing');
});

// authentication
Route::post('/auth/register', [AuthController::class, 'register'])->middleware(['throttle:5,1', 'guest']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware(['throttle:10,1', 'guest']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
// authentication end



Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/summary', [SummaryController::class, 'summary'])->name('summary');

    Route::get('/fokus', function () {
        return redirect('/pomodoro');
    });
    
    Route::get('/catatan', function () {
        return redirect('/utama');
    });
    
    Route::get('/belajar', function () {
        return view('belajar');
    });
    Route::get('/pomodoro', function () {
        return view('pomodoro');
    });
    Route::get('/utama', function () {
        return view('utama');
    })->name('utama');
});

Route::middleware(['auth', 'admin.validate'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});
