<?php

use App\Http\Controllers\Api\PriceSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ *** تم تصحيح هذا الجزء ***
Route::prefix('v1')->group(function () {
    // المسار الصحيح هو /api/v1/best-offer
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});
