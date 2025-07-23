<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Products Routes
Route::apiResource('products', ProductController::class);

// Customers Routes
Route::apiResource('customers', CustomerController::class);

// Orders Routes
Route::apiResource('orders', OrderController::class)->except(['destroy']);