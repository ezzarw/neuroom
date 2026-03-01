<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/auth/register', [AuthController::class, "register"])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, "login"])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'admin.validate'])->group(function () {
    Route::get('/admin/user-view', [AdminController::class, "user_view"]);
    Route::post('/admin/user-add', [AdminController::class, "user_add"]);
    Route::put('/admin/user-edit', [AdminController::class, "user_edit"]);
    Route::delete('/admin/user-delete', [AdminController::class, "user_delete"]);
});
