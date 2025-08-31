<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product' => 'required|string|min:3|max:255',
                'country' => 'required|string|size:2',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $productName = $request->input('product');
            $countryCode = $request->input('country');

            $product = Product::where('name', 'like', '%' . $productName . '%')->first();

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $cheapestOffer = $product->priceOffers()
                ->join('stores', 'price_offers.store_id', '=', 'stores.id')
                ->where('stores.country_code', $countryCode)
                ->orderBy('price', 'asc')
                ->select('price_offers.*', 'stores.name as store_name', 'stores.country_code')
                ->first();

            if (!$cheapestOffer) {
                return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
            }

            return response()->json($cheapestOffer);
        } catch (\Throwable $e) {
            Log::error("PriceSearchController@bestOffer failed: " . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
