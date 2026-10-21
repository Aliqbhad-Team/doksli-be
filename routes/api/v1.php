<?php

use App\Http\Controllers\Api\V1\AuthController;
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

Route::apiResource('categories', CategoryController::class);

// Roles / Units / Users - public read/create, protected update/delete via Policy
// Protected via JWT Auth
Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('tags', TagController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('document-versions', DocumentVersionController::class);
});

// Roles / Units / Users - public read/create, protected update/delete via Policy (403 for guest)
Route::apiResource('roles', RoleController::class);
Route::apiResource('units', UnitController::class);

// Folder Management
Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('folders', FolderController::class);
    Route::apiResource('folder-permissions', FolderPermissionController::class);
});

Route::apiResource('documents', DocumentController::class);
Route::apiResource('document-versions', DocumentVersionController::class);

// Modul User
Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{user}', 'show')->name('show');
    Route::match(['put', 'patch'], '{user}', 'update')->name('update');
    Route::delete('{user}', 'destroy')->name('destroy');
});
Route::apiResource('groups', GroupController::class);
Route::apiResource('folders', FolderController::class);
Route::apiResource('folder-permissions', FolderPermissionController::class);

// Users - protected by jwt.auth, authorize via StoreUserRequest + Policy
Route::middleware('jwt.auth')->group(function () {
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{user}', 'show')->name('show');
        Route::match(['put', 'patch'], '{user}', 'update')->name('update');
        Route::delete('{user}', 'destroy')->name('destroy');
    });

    // User <-> Groups pivot - protected
    Route::controller(UserGroupController::class)->group(function () {
        Route::get('users/{user}/groups', 'indexByUser')->name('users.groups.index');
        Route::post('users/{user}/groups', 'store')->name('users.groups.store');
        Route::delete('users/{user}/groups/{group}', 'destroy')->name('users.groups.destroy');
        Route::delete('users/{user}/groups', 'destroyMany')->name('users.groups.destroyMany');
        Route::get('groups/{group}/users', 'indexByGroup')->name('groups.users.index');
    });
});
