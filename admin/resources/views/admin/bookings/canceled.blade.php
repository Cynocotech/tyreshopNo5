<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <span>Canceled Bookings</span>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">← Back to Calendar</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <p class="text-sm text-slate-600">Bookings that were canceled. They no longer appear on the calendar but remain in this list for records.</p>
        </div>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
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
                    <td colspan="6" class="px-4 py-12 text-center text-slate-500">No canceled bookings.</td>
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
</x-admin-layout>
