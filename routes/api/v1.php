<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentVersionController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\FolderPermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('units', UnitController::class);
Route::apiResource('folders', FolderController::class);
Route::apiResource('folder-permissions', FolderPermissionController::class);
Route::apiResource('documents', DocumentController::class);
Route::apiResource('document-versions', DocumentVersionController::class);

Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{user}', 'show')->name('show');
    Route::match(['put', 'patch'], '{user}', 'update')->name('update');
    Route::delete('{user}', 'destroy')->name('destroy');
});
