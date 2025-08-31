<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PriceSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());

// ✅ *** هذه هي المسارات الصحيحة والنهائية ***
Route::prefix('v1')->group(function () {
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});
