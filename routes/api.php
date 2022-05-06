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
Route::post('re-code', [AuthController::class, 'recode']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group( function () {
    Route::get('products', [ProductController::class, 'products']);
    Route::post('add-location', [AuthController::class, 'addLocation']);
    Route::post('update-location', [AuthController::class, 'updateLocation']);
    Route::post('create-category', [ProductController::class, 'createCategory']);
    Route::get('list-home', [ProductController::class, 'listHome']);
    Route::get('list-food', [ProductController::class, 'listFood']);
    Route::get('detail-food/{id}', [ProductController::class, 'detailFood']);

});