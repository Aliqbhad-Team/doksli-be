<?php
use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('v1.')->group(base_path('routes/api/v1.php'));
