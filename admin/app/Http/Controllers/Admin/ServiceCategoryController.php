<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Database\Seeders\ServiceCategorySeeder;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->get();

        // Auto-seed default categories if none exist (no redirect - avoids redirect loop if seeder fails)
        if ($categories->isEmpty()) {
            try {
                (new ServiceCategorySeeder)->run();
                $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->get();
                session()->flash('success', 'Default categories have been created.');
            } catch (\Throwable $e) {
                session()->flash('error', 'No categories yet. Add one below, or run: php artisan db:seed --class=ServiceCategorySeeder');
            }
        }

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new ServiceCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:service_categories,slug',
            'label' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        ServiceCategory::create($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(Request $request, ServiceCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:service_categories,slug,' . $category->id,
            'label' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if ($category->services()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category with services. Move or delete services first.');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
