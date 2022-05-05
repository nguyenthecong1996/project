<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('verify', [AuthController::class, 'verify']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group( function () {
    Route::get('products', [ProductController::class, 'products']);
    Route::get('add-location', [AuthController::class, 'addLocation']);
    Route::get('update-location', [AuthController::class, 'updateLocation']);

});