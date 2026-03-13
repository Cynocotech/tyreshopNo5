<x-admin-layout>
    <x-slot name="header">Services</x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Service</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Icon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Price</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($services as $s)
                <tr>
                    <td class="px-4 py-3 text-2xl">{{ $s->icon ?? '–' }}</td>
                    <td class="px-4 py-3 font-medium">{{ $s->title }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $s->category?->label ?? '–' }}</td>
                    <td class="px-4 py-3">{{ $s->is_quote ? 'Quote' : '£' . number_format($s->price, 2) }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.services.edit', $s) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.services.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Delete this service?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No services yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
