<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.product-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.product-categories.form', ['category' => new ProductCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        ProductCategory::create($validated);
        return redirect()->route('admin.product-categories.index')->with('success', 'Category created.');
    }

    public function edit(ProductCategory $product_category): View
    {
        return view('admin.product-categories.form', ['category' => $product_category]);
    }

    public function update(Request $request, ProductCategory $product_category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug,' . $product_category->id,
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $product_category->update($validated);
        return redirect()->route('admin.product-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $product_category): RedirectResponse
    {
        $product_category->delete();
        return redirect()->route('admin.product-categories.index')->with('success', 'Category deleted.');
    }
}
