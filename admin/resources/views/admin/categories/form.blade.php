<x-admin-layout>
    <x-slot name="header">{{ $category->exists ? 'Edit Category' : 'Add Category' }}</x-slot>

    <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-md">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="w-full rounded border-slate-300" required placeholder="servicing-mot">
                @error('slug')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label</label>
                <input type="text" name="label" value="{{ old('label', $category->label) }}" class="w-full rounded border-slate-300" required placeholder="Servicing & MOT">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="w-24 rounded border-slate-300">
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</x-admin-layout>
