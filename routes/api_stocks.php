<?php

use App\Http\Controllers\ApiController;
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
Route::post('/signup', [ApiController::class, 'register']);

Route::middleware('auth:sanctum')->group(function() {
    Route::patch('/profile', [ApiController::class, 'updateProfile']);
    Route::get('/countries', [ApiController::class, 'countries']);

    Route::post('/onboard/complete', [StocksController::class, 'createAccount']);

    Route::get('account/trading-profile', [StocksController::class, 'getTradingAccount']);
    Route::get('account/portfolio/history', [StocksController::class, 'getPortfolioHistory']);
    Route::get('positions', [StocksController::class, 'getPositions']);
    Route::get('position/{symbol}', [StocksController::class, 'getPosition']);
    Route::get('quote/{symbol}', [StocksController::class, 'getQuote']);

    Route::get('/plaid/create_link_token', [StocksController::class, 'createPlaidLinkToken']);
    Route::post('/plaid/connect', [StocksController::class, 'connectPlaid']);

    Route::get('/assets/{class}', [StocksController::class, 'searchAssets']);

    Route::post('/order/create', [StocksController::class, 'createOrder']);
    Route::post('/order/cancel', [StocksController::class, 'cancelOrder']);

    Route::post('/fund', [StocksController::class, 'fund']);
    Route::post('/withdraw', [StocksController::class, 'withdraw']);
    Route::get('/transfer', [StocksController::class, 'getTransferHistory']);

    Route::get('watchlist', [StocksController::class, 'getWatchList']);
    Route::post('watchlist', [StocksController::class, 'setWatchList']);
    Route::delete('watchlist/{symbol}', [StocksController::class, 'removeAssetFromWatchList']);

});

Route::get('ach_relationships', [StocksController::class, 'getAchRelationships']);
