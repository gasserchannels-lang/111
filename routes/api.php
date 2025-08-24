<?php

use App\Http\Controllers\Api\PriceSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});
