<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <span>Canceled Bookings</span>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">← Back to Calendar</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.bookings.bulk-delete') }}" id="canceled-bulk-form" onsubmit="return confirm('Permanently delete selected canceled bookings? This cannot be undone.');">
        @csrf
        <div class="mb-3 flex items-center gap-3">
            <button type="submit" id="canceled-delete-btn" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">Delete selected</button>
            <span class="text-sm text-slate-500" id="canceled-selected-count">0 selected</span>
        </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <p class="text-sm text-slate-600">Bookings that were canceled. They no longer appear on the calendar but remain in this list for records.</p>
        </div>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left w-10">
                        <input type="checkbox" id="canceled-select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select all">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Booking ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Date & Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Canceled</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($bookings as $b)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $b->id }}" class="canceled-row-cb rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $b->booking_id }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $b->customer_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $b->appointment_date->format('d/m/Y') }} {{ $b->appointment_time }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $b->service_type ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $b->total_amount ? '£' . number_format((float)$b->total_amount, 2) : '—' }}</td>
                    <td class="px-4 py-3 text-sm text-red-600">{{ $b->canceled_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">No canceled bookings.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('canceled-bulk-form');
        if (!form) return;
        var selectAll = document.getElementById('canceled-select-all');
        var checkboxes = form.querySelectorAll('.canceled-row-cb');
        var deleteBtn = document.getElementById('canceled-delete-btn');
        var countSpan = document.getElementById('canceled-selected-count');
        function update() {
            var n = form.querySelectorAll('.canceled-row-cb:checked').length;
            deleteBtn.disabled = n === 0;
            countSpan.textContent = n + ' selected';
            if (selectAll) selectAll.checked = n > 0 && n === checkboxes.length;
        }
        if (selectAll) selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            update();
        });
        checkboxes.forEach(function(cb) { cb.addEventListener('change', update); });
    });
    </script>
</x-admin-layout>
