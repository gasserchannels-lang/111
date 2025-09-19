<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product management endpoints"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get all products",
     *     description="Retrieve a paginated list of all products",
     *     operationId="getProducts",
     *     tags={"Products"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for product name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Filter by brand ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50); // Limit max per page

        $query = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'category_id', 'brand_id', 'description'])
            ->with([
                'brand:id,name',
                'category:id,name',
                'priceOffers.store:id,name',
            ])
            ->where('is_active', true)
            ->orderBy('id', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            if (is_string($searchTerm)) {
                $query->where('name', 'like', "%{$searchTerm}%");
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Use pagination for better performance
        $products = $query->paginate($perPage);

        // Transform data efficiently
        $data = $products->getCollection()->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description ?? '',
                'slug' => $product->slug,
                'price' => $product->price,
                'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
                'stores' => $product->priceOffers->map(function ($offer) {
                    return [
                        'id' => $offer->store?->id ?? $offer->id,
                        'name' => $offer->store?->name ?? 'Store'.$offer->id,
                        'price' => $offer->price,
                        'is_available' => $offer->is_available ?? true,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'data' => $data,
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get product by ID",
     *     description="Retrieve a specific product by its ID",
     *     operationId="getProductById",
     *     tags={"Products"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProductDetail")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        // Use caching for better performance
        $cacheKey = 'product_'.$id;
        $productData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($id) {
            $product = Product::with([
                'brand:id,name',
                'category:id,name',
                'priceOffers' => function ($query) {
                    $query->select(['id', 'product_id', 'price', 'store_id', 'is_available', 'store_url'])
                        ->with('store:id,name')
                        ->where('is_available', true)
                        ->orderBy('price', 'asc')
                        ->limit(10); // Limit price offers for better performance
                },
                'reviews' => function ($query) {
                    $query->select(['id', 'product_id', 'rating', 'comment'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5); // Limit reviews for better performance
                },
            ])
                ->where('is_active', true)
                ->findOrFail($id);

            // Transform to include stores properly
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->slug,
                'price' => $product->price,
                'image' => $product->image,
                'is_active' => $product->is_active,
                'is_featured' => $product->is_featured,
                'stock_quantity' => $product->stock_quantity,
                'category' => $product->category,
                'brand' => $product->brand,
                'stores' => $product->priceOffers->map(function ($offer) {
                    return [
                        'id' => $offer->store?->id ?? $offer->id,
                        'name' => $offer->store?->name ?? 'Unknown Store',
                        'price' => $offer->price,
                        'is_available' => $offer->is_available ?? true,
                    ];
                })->toArray(),
                'reviews' => $product->reviews,
            ];
        });

        return response()->json([
            'data' => $productData,
            'message' => 'Product retrieved successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     description="Create a new product (Admin only)",
     *     operationId="createProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProductCreateRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string',
                'is_active' => 'boolean',
                'category_id' => 'nullable|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'stock_quantity' => 'nullable|integer|min:0',
                'stores' => 'nullable|array',
                'images' => 'nullable|array',
            ]);

            // If category_id or brand_id not provided, use defaults or create them
            if (empty($validated['category_id'])) {
                $category = \App\Models\Category::first();
                $validated['category_id'] = $category ? $category->id : 1;
            }

            if (empty($validated['brand_id'])) {
                $brand = \App\Models\Brand::first();
                $validated['brand_id'] = $brand ? $brand->id : 1;
            }

            // Set default values
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;

            // Generate unique slug from name
            $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure slug is unique
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $validated['slug'] = $slug;
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;

            $product = Product::create($validated);

            // Handle stores if provided
            if (! empty($validated['stores'])) {
                $stores = $validated['stores'];
                foreach ($stores as $index => $storeName) {
                    // Find or create store
                    $store = \App\Models\Store::where('name', $storeName)->first();
                    if (! $store) {
                        $store = \App\Models\Store::create([
                            'name' => $storeName,
                            'slug' => \Illuminate\Support\Str::slug($storeName),
                            'is_active' => true,
                            'priority' => 10 - $index,
                        ]);
                    }

                    // Create price offer for this store
                    \App\Models\PriceOffer::create([
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'price' => $validated['price'],
                        'is_available' => true,
                        'store_url' => 'https://example.com/'.$store->slug,
                    ]);
                }
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->update(['image' => $imagePath]);
            }

            // Load relationships for response
            $product->load(['brand:id,name', 'category:id,name', 'priceOffers.store:id,name']);

            return response()->json([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                    'is_active' => $product->is_active,
                    'stock_quantity' => $product->stock_quantity,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'slug' => $product->slug,
                    'stores' => $product->priceOffers->map(function ($offer) {
                        return [
                            'id' => $offer->store?->id ?? $offer->id,
                            'name' => $offer->store?->name ?? 'Unknown Store',
                            'price' => $offer->price,
                            'is_available' => $offer->is_available ?? true,
                        ];
                    })->toArray(),
                    'store_ids' => $product->priceOffers->pluck('store_id')->filter()->unique()->values()->toArray(),
                ],
                'message' => 'Product created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     summary="Update product",
     *     description="Update an existing product (Admin only)",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'image' => 'nullable|string',
                'is_active' => 'boolean',
                'category_id' => 'sometimes|exists:categories,id',
                'brand_id' => 'sometimes|exists:brands,id',
            ]);

            // Update slug if name is being updated
            if (isset($validated['name'])) {
                $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
                $slug = $baseSlug;
                $counter = 1;

                // Ensure slug is unique (excluding current product)
                while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $validated['slug'] = $slug;
            }

            $product->update($validated);

            return response()->json([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                    'is_active' => $product->is_active,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                ],
                'message' => 'Product updated successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     summary="Delete product",
     *     description="Delete a product (Admin only)",
     *     operationId="deleteProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
