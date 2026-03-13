<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSerial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::withCount(['serials', 'availableSerials'])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'sku' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'requires_serial' => 'boolean',
            'quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50|in:' . implode(',', array_keys(\App\Models\Product::iconOptions())),
        ]);
        $validated['requires_serial'] = $request->boolean('requires_serial');
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['icon'] = !empty($validated['icon'] ?? '') ? $validated['icon'] : null;
        $product = Product::create($validated);

        if ($product->requires_serial) {
            $serials = array_filter(array_map('trim', explode("\n", $request->input('serials', ''))));
            foreach ($serials as $sn) {
                if ($sn) {
                    ProductSerial::create(['product_id' => $product->id, 'serial_number' => $sn]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load('serials');
        return view('admin.products.form', ['product' => $product]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'sku' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'requires_serial' => 'boolean',
            'quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50|in:' . implode(',', array_keys(\App\Models\Product::iconOptions())),
        ]);
        $validated['requires_serial'] = $request->boolean('requires_serial');
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['icon'] = !empty($validated['icon'] ?? '') ? $validated['icon'] : null;
        $product->update($validated);

        if ($product->requires_serial) {
            $serials = array_filter(array_map('trim', explode("\n", $request->input('serials', ''))));
            $existing = $product->serials->pluck('serial_number')->toArray();
            foreach ($serials as $sn) {
                if ($sn && !in_array($sn, $existing)) {
                    ProductSerial::firstOrCreate(['product_id' => $product->id, 'serial_number' => $sn]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
