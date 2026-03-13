<x-admin-layout>
    <x-slot name="header">Areas Served</x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.areas.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Area</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Sort Order</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($areas as $area)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $area->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $area->sort_order }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.areas.edit', $area) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="inline" onsubmit="return confirm('Delete this area?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">No areas yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
