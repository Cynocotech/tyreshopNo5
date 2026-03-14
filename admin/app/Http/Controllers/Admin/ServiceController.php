<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::with('category')->orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', [
            'service' => new Service,
            'categories' => ServiceCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:services,slug',
            'value' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:20',
            'price' => 'required|numeric|min:0',
            'hero_mot_price' => 'nullable|numeric|min:0',
            'price_label' => 'required|string|max:255',
            'price_display' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'is_quote' => 'boolean',
            'keywords' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'combo_badge' => 'nullable|string|max:100',
            'combo_subtitle' => 'nullable|string|max:255',
            'combo_features' => 'nullable|string',
            'combo_saving' => 'nullable|string|max:100',
            'is_combo_hot' => 'boolean',
        ]);
        $validated['is_quote'] = $request->boolean('is_quote');
        $validated['is_combo_hot'] = $request->boolean('is_combo_hot');
        $validated['keywords'] = $validated['keywords'] ? array_map('trim', explode("\n", $validated['keywords'])) : [];
        $validated['combo_features'] = !empty($validated['combo_features']) ? array_map('trim', array_filter(explode("\n", $validated['combo_features']))) : null;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        Service::create($validated);
        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', [
            'service' => $service,
            'categories' => ServiceCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:services,slug,' . $service->id,
            'value' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:20',
            'price' => 'required|numeric|min:0',
            'hero_mot_price' => 'nullable|numeric|min:0',
            'price_label' => 'required|string|max:255',
            'price_display' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'is_quote' => 'boolean',
            'keywords' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'combo_badge' => 'nullable|string|max:100',
            'combo_subtitle' => 'nullable|string|max:255',
            'combo_features' => 'nullable|string',
            'combo_saving' => 'nullable|string|max:100',
            'is_combo_hot' => 'boolean',
        ]);
        $validated['is_quote'] = $request->boolean('is_quote');
        $validated['is_combo_hot'] = $request->boolean('is_combo_hot');
        $validated['keywords'] = $validated['keywords'] ? array_map('trim', explode("\n", $validated['keywords'])) : [];
        $validated['combo_features'] = !empty($validated['combo_features']) ? array_map('trim', array_filter(explode("\n", $validated['combo_features']))) : null;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $service->update($validated);
        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }
}
