<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListProductController;
use App\Http\Controllers\OrdersController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/index', [ListProductController::class, 'index']);
Route::get('/store', [ListProductController::class, 'store']);
Route::delete('/delete/{id}', [ListProductController::class, 'delete']);
Route::delete('/lp/destroy', [ListProductController::class, 'destroy']);

Route::get('/indexSP', [ListProductController::class, 'indexSP']);
Route::get('/storeSP', [ListProductController::class, 'storeSP']);

Route::get('/indexP', [ListProductController::class, 'indexP']);
Route::get('/storeP', [ListProductController::class, 'storeP']);

Route::post('/order/create', [OrdersController::class, 'create']);
Route::delete('/destroy', [OrdersController::class, 'destroy']);
Route::get('/order/bill/{id}', [OrdersController::class, 'bill']);
