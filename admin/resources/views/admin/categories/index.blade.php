<x-admin-layout>
    <x-slot name="header">Service Categories</x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Category</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Label</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Services</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($categories as $c)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $c->label }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $c->slug }}</td>
                    <td class="px-4 py-3">{{ $c->services_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.categories.edit', $c) }}" class="text-blue-600 hover:underline">Edit</a>
                        @if($c->services_count == 0)
                        <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">No categories yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
