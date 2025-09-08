<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $brands = Brand::with('products')->paginate(20);

        return view('brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'slug' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        Brand::create($request->all());

        return redirect()->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): View
    {
        $brand->load('products');

        return view('brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand): View
    {
        return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,'.$brand->id,
            'slug' => 'required|string|max:255|unique:brands,slug,'.$brand->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $brand->update($request->all());

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->forceDelete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
