<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DataPlanController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\TopUpController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TransferController;
use App\Http\Controllers\API\TransferHistoryController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\WebhookController;
use App\Http\Middleware\JwtMiddleware;
use App\Models\OperatorCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/webhooks', [WebhookController::class, 'update']);

Route::group(['middleware' => 'jwt.verify'], function ($router) {
    Route::post('top_ups', [TopUpController::class, 'store']);
    Route::post('transfers', [TransferController::class, 'store']);
    Route::post('data_plans', [DataPlanController::class, 'store']);
    Route::post('operator_cards', [OperatorCard::class, 'index']);
    Route::get('payment_methods', [PaymentMethodController::class, 'index']);
    Route::get('transfer_histories', [TransferHistoryController::class, 'index']);
    Route::get('transactions', [TransactionController::class, 'index']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::get('/{username}', [UserController::class, 'getUserByUsername']);
        Route::put('/', [UserController::class, 'update']);
    });

    Route::prefix('wallets')->group(function () {
        Route::put('wallets', [WalletController::class, 'update']);
        Route::get('wallets', [WalletController::class, 'show']);
    });

    Route::post('logout', [AuthController::class, 'logout']);
});
