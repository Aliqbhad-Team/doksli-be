<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentVersionController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\FolderPermissionController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserGroupController;
use Illuminate\Support\Facades\Route;

// Public
Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('refresh', 'refresh')->name('refresh');
});

// Protected (JWT)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
        Route::get('me', 'me')->name('me');
        Route::post('logout', 'logout')->name('logout');
    });

    Route::apiResources([
        'categories' => CategoryController::class,
        'roles' => RoleController::class,
        'units' => UnitController::class,
        'groups' => GroupController::class,
        'tags' => TagController::class,
        'documents' => DocumentController::class,
        'document-versions' => DocumentVersionController::class,
        'folders' => FolderController::class,
        'folder-permissions' => FolderPermissionController::class,
        'users' => UserController::class,
    ]);

    // User <-> Group pivot
    Route::controller(UserGroupController::class)->group(function () {
        Route::get('users/{user}/groups', 'indexByUser')->name('users.groups.index');
        Route::post('users/{user}/groups', 'store')->name('users.groups.store');
        Route::delete('users/{user}/groups', 'destroyMany')->name('users.groups.destroyMany');
        Route::delete('users/{user}/groups/{group}', 'destroy')->name('users.groups.destroy');
        Route::get('groups/{group}/users', 'indexByGroup')->name('groups.users.index');
    });
});
