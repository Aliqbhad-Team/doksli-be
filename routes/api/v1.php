<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// Public auth
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::middleware('jwt.auth')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// Roles / Units / Users - public read/create, protected update/delete via Policy (403 for guest)
Route::apiResource('roles', RoleController::class);
Route::apiResource('units', UnitController::class);
Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{user}', 'show')->name('show');
    Route::match(['put', 'patch'], '{user}', 'update')->name('update');
    Route::delete('{user}', 'destroy')->name('destroy');
});
