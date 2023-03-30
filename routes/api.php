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
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class,'userDetails'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
Route::get('/index', [ProductController::class, 'index']);
Route::post('/store', [ProductController::class, 'store']);
Route::get('/show{id}', [ProductController::class, 'show']);
Route::post('/update{id}', [ProductController::class, 'update']);
Route::delete('/destroy{id}', [ProductController::class, 'destroy']);

});