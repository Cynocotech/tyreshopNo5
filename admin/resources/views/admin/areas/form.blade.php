<x-admin-layout>
    <x-slot name="header">{{ $area->exists ? 'Edit Area' : 'Add Area' }}</x-slot>

    <form action="{{ $area->exists ? route('admin.areas.update', $area) : route('admin.areas.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        @if($area->exists) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Area Name</label>
                <input type="text" name="name" value="{{ old('name', $area->name) }}" class="w-full rounded border-slate-300" required placeholder="Palmers Green">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $area->sort_order ?? 0) }}" class="w-24 rounded border-slate-300">
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.areas.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</x-admin-layout>
