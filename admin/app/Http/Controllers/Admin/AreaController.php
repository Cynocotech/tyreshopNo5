<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        return view('admin.areas.index', [
            'areas' => Area::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.areas.form', ['area' => new Area]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        Area::create($validated);
        return redirect()->route('admin.areas.index')->with('success', 'Area added.');
    }

    public function edit(Area $area): View
    {
        return view('admin.areas.form', ['area' => $area]);
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $area->update($validated);
        return redirect()->route('admin.areas.index')->with('success', 'Area updated.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $area->delete();
        return redirect()->route('admin.areas.index')->with('success', 'Area deleted.');
    }
}
