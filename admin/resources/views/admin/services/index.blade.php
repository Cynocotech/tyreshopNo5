<x-admin-layout>
    <x-slot name="header">Services</x-slot>

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Service</a>
        <form id="bulk-delete-form" action="{{ route('admin.services.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete all selected services? This cannot be undone.')">
            @csrf
            <button id="bulk-delete-btn" type="submit" disabled class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">Delete Selected</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form id="service-select-form" action="{{ route('admin.services.bulk-delete') }}" method="POST">
            @csrf
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">
                        <input id="select-all-services" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select all services" />
                    </th>
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
                    <td class="px-4 py-3 align-top">
                        <input type="checkbox" name="service_ids[]" value="{{ $s->id }}" class="service-select rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select {{ $s->title }}" form="bulk-delete-form" />
                    </td>
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
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No services yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </form>
    </div>

    <script>
        (function () {
            var selectAll = document.getElementById('select-all-services');
            var bulkBtn = document.getElementById('bulk-delete-btn');
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.service-select'));
            if (!selectAll || !bulkBtn || boxes.length === 0) return;

            function updateUi() {
                var checkedCount = boxes.filter(function (b) { return b.checked; }).length;
                bulkBtn.disabled = checkedCount === 0;
                selectAll.checked = checkedCount > 0 && checkedCount === boxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
            }

            selectAll.addEventListener('change', function () {
                boxes.forEach(function (b) { b.checked = selectAll.checked; });
                updateUi();
            });
            boxes.forEach(function (b) { b.addEventListener('change', updateUi); });
            updateUi();
        })();
    </script>
</x-admin-layout>
