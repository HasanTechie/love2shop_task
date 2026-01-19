<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('orders.index');
});

Route::get('/orders', [OrderController::class, 'index']);
