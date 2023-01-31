<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
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
    Route::post('/onboard/complete', [ApiController::class, 'completeOnboard']);
    Route::get('/plaid/create_link_token', [ApiController::class, 'createPlaidLinkToken']);
    Route::post('/plaid/connect', [ApiController::class, 'connectPlaid']);
});
