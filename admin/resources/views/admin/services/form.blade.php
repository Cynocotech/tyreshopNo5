<x-admin-layout>
    <x-slot name="header">{{ $service->exists ? 'Edit Service' : 'Add Service' }}</x-slot>

    <form action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-2xl">
        @csrf
        @if($service->exists) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Slug (ID)</label>
                <input type="text" name="slug" value="{{ old('slug', $service->slug) }}" class="w-full rounded border-slate-300" required>
                @error('slug')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Value</label>
                <input type="text" name="value" value="{{ old('value', $service->value) }}" class="w-full rounded border-slate-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $service->title) }}" class="w-full rounded border-slate-300" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', $service->icon) }}" class="w-full rounded border-slate-300" placeholder="🛞">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select name="service_category_id" class="w-full rounded border-slate-300" required>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('service_category_id', $service->service_category_id) == $c->id ? 'selected' : '' }}>{{ $c->label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Price (£)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $service->price ?? 0) }}" class="w-full rounded border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Hero MOT Price (£, optional)</label>
                    <input type="number" name="hero_mot_price" step="0.01" value="{{ old('hero_mot_price', $service->hero_mot_price) }}" class="w-full rounded border-slate-300" placeholder="19">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Price Label</label>
                    <input type="text" name="price_label" value="{{ old('price_label', $service->price_label) }}" class="w-full rounded border-slate-300" required placeholder="£54.85">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Price Display</label>
                    <input type="text" name="price_display" value="{{ old('price_display', $service->price_display) }}" class="w-full rounded border-slate-300" required placeholder="£54.85">
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_quote" value="1" {{ old('is_quote', $service->is_quote ?? false) ? 'checked' : '' }}>
                    <span class="text-sm text-slate-700">Quote only (no fixed price)</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Keywords (one per line)</label>
                <textarea name="keywords" rows="4" class="w-full rounded border-slate-300">{{ old('keywords', implode("\n", $service->keywords ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}" class="w-24 rounded border-slate-300">
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</x-admin-layout>
