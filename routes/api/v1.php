<?php

use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('roles', RoleController::class);
Route::apiResource('units', UnitController::class);

Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{user}', 'show')->name('show');
    Route::match(['put', 'patch'], '{user}', 'update')->name('update');
    Route::delete('{user}', 'destroy')->name('destroy');
});
