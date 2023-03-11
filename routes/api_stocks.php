<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\StocksController;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [ApiController::class, 'authenticate']);
Route::post('/login/google', [ApiController::class, 'googleSign']);
Route::post('/signup', [ApiController::class, 'register']);
Route::post('/validate/otp', [OtpController::class, 'validateOtp']);
Route::post('/resend/otp', [OtpController::class, 'resendOtp']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'mobile']);
Route::post('/reset-password', [ResetPasswordController::class, 'mobile']);

Route::middleware('auth:sanctum')->group(function() {
    Route::patch('/profile', [ApiController::class, 'updateProfile']);
    Route::get('/countries', [ApiController::class, 'countries']);

    Route::post('/upload/document', [ApiController::class, 'uploadDocument']);
    Route::post('/onboard/complete', [StocksController::class, 'createAccount']);

    Route::get('account/trading-profile', [StocksController::class, 'getTradingAccount']);
    Route::get('account/portfolio/history', [StocksController::class, 'getPortfolioHistory']);
    Route::get('positions', [StocksController::class, 'getPositions']);
    Route::get('activities', [StocksController::class, 'getActivities']);
    Route::get('position/{symbol}', [StocksController::class, 'getPosition']);
    Route::get('quote', [StocksController::class, 'getQuote']);

    Route::get('/plaid/create_link_token', [StocksController::class, 'createPlaidLinkToken']);
    Route::post('/plaid/connect', [StocksController::class, 'connectPlaid']);

    Route::get('/assets/{class}', [StocksController::class, 'searchAssets']);

    Route::post('/order/create', [StocksController::class, 'createOrder']);
    Route::post('/order/{order_id}/cancel', [StocksController::class, 'cancelOrder']);
    Route::get('orders', [StocksController::class, 'getOrders']);

    Route::post('/fund', [StocksController::class, 'fund']);
    Route::post('/withdraw', [StocksController::class, 'withdraw']);
    Route::get('/transfer', [StocksController::class, 'getTransferHistory']);

    Route::get('watchlist', [StocksController::class, 'getWatchList']);
    Route::post('watchlist', [StocksController::class, 'setWatchList']);
    Route::delete('watchlist/{symbol}', [StocksController::class, 'removeAssetFromWatchList']);

    Route::get('market/bars', [StocksController::class, 'getMarketDataBars']);

    Route::get('ach_relationships', [StocksController::class, 'getAchRelationships']);
    Route::delete('ach_relationship', [StocksController::class, 'deleteAchRelationship']);
    Route::delete('bank', [StocksController::class, 'deleteBank']);

    Route::post('device_token', [ApiController::class, 'setDeviceToken']);

    Route::get('notifications', [StocksController::class, 'getNotifications']);
    Route::get('notifications/new', [StocksController::class, 'getNewNotifications']);
    Route::delete('notifications', [StocksController::class, 'deleteAllNotifications']);
    Route::delete('notifications/{id}', [StocksController::class, 'deleteNotification']);
    Route::post('notifications/{id}/read', [StocksController::class, 'markAsReadNotification']);
    Route::post('notifications/read', [StocksController::class, 'markAsReadAllNotifications']);
});

Route::get('accounts', [StocksController::class, 'checkAccounts']);
Route::get('orders/check', [StocksController::class, 'checkOpenOrders']);
Route::get('stocks/top', [StocksController::class, 'getTopStocks']);
