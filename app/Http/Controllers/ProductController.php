<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
	public function show(string $slug): View
	{
		$product = Product::query()->where('slug', $slug)->firstOrFail();

		return view('product-show', [
			'product' => $product,
		]);
	}
}


