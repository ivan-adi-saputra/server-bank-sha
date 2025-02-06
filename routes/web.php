<?php

use App\Http\Controllers\Admin\RedirectPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment_finish', [RedirectPaymentController::class, 'finish']);
