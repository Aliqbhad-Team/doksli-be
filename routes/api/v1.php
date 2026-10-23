<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentVersionController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\FolderPermissionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserGroupController;
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

// Roles / Tags / Categories / Units / Groups - public read
Route::apiResource('roles', RoleController::class);
Route::apiResource('tags', TagController::class);
Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('units', [UnitController::class, 'index'])->name('units.index');
Route::get('units/{unit}', [UnitController::class, 'show'])->name('units.show');
Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
Route::get('groups/{group}', [GroupController::class, 'show'])->name('groups.show');
Route::apiResource('folders', FolderController::class);
Route::apiResource('folder-permissions', FolderPermissionController::class);
Route::apiResource('documents', DocumentController::class);
Route::apiResource('document-versions', DocumentVersionController::class);

// Protected routes by jwt.auth
Route::middleware('jwt.auth')->group(function () {
    // Category mutating routes
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::match(['put', 'patch'], 'categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Unit mutating routes
    Route::post('units', [UnitController::class, 'store'])->name('units.store');
    Route::match(['put', 'patch'], 'units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Group mutating routes
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::match(['put', 'patch'], 'groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

    // Users
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{user}', 'show')->name('show');
        Route::match(['put', 'patch'], '{user}', 'update')->name('update');
        Route::delete('{user}', 'destroy')->name('destroy');
    });

    // User <-> Groups pivot
    Route::controller(UserGroupController::class)->group(function () {
        Route::get('users/{user}/groups', 'indexByUser')->name('users.groups.index');
        Route::post('users/{user}/groups', 'store')->name('users.groups.store');
        Route::delete('users/{user}/groups/{group}', 'destroy')->name('users.groups.destroy');
        Route::delete('users/{user}/groups', 'destroyMany')->name('users.groups.destroyMany');
        Route::get('groups/{group}/users', 'indexByGroup')->name('groups.users.index');
    });
});