<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BasicApiController;
use App\Http\Controllers\Api\ProductController;

// Route Products
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/discount', [ProductController::class, 'applyDiscount']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'updatePut']);
Route::patch('/products/{id}', [ProductController::class, 'updatePatch']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);