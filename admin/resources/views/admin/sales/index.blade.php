<x-admin-layout>
    <x-slot name="header">Sales</x-slot>

    {{-- Date filter & nav tabs --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.sales.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-700">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-slate-300 text-sm py-1.5">
            <label class="text-sm font-medium text-slate-700">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-slate-300 text-sm py-1.5">
            <button type="submit" class="px-3 py-1.5 bg-slate-700 text-white rounded-lg text-sm font-medium hover:bg-slate-800">Apply</button>
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.sales.index', ['from' => $from, 'to' => $to]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-600 text-white">List</a>
            <a href="{{ route('admin.sales.journal', ['from' => $from, 'to' => $to]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-slate-200 text-slate-800 hover:bg-slate-300">Journal</a>
            <a href="{{ route('admin.sales.daily', ['from' => $from, 'to' => $to]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-slate-200 text-slate-800 hover:bg-slate-300">Daily Summary</a>
            <a href="{{ route('admin.sales.export', ['from' => $from, 'to' => $to]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700">Export CSV</a>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <h3 class="text-slate-500 text-sm font-medium">Total revenue</h3>
            <p class="text-2xl font-bold text-slate-800">£{{ number_format($summary['grand_total'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-slate-500">
            <h3 class="text-slate-500 text-sm font-medium">Transactions</h3>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($summary['transaction_count']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
            <h3 class="text-slate-500 text-sm font-medium">Avg sale</h3>
            <p class="text-2xl font-bold text-slate-800">
                £{{ $summary['transaction_count'] > 0 ? number_format($summary['grand_total'] / $summary['transaction_count'], 2) : '0.00' }}
            </p>
        </div>
    </div>

    {{-- Payment method breakdown --}}
    @if($summary['by_method']->isNotEmpty())
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-slate-700 mb-2">By payment method</h3>
        <div class="flex flex-wrap gap-4">
            @foreach($summary['by_method'] as $method => $row)
            <div class="flex items-baseline gap-2">
                <span class="text-slate-600 capitalize">{{ str_replace('_', ' ', $method ?: 'N/A') }}</span>
                <span class="font-semibold text-slate-800">£{{ number_format((float) $row->total, 2) }}</span>
                <span class="text-slate-400 text-sm">({{ $row->count }})</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Sales table --}}
    <form method="POST" action="{{ route('admin.sales.bulk-delete') }}" id="sales-bulk-form" onsubmit="return confirm('Delete selected sales? This cannot be undone.');">
        @csrf
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <div class="mb-3 flex items-center gap-3">
            <button type="submit" id="sales-delete-btn" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Delete selected
            </button>
            <span class="text-sm text-slate-500" id="sales-selected-count">0 selected</span>
        </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="sales-select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select all">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Reference</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Items</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Payment</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($sales as $sale)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $sale->id }}" class="sales-row-cb rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->completed_at?->format('d/m/Y H:i') ?? '–' }}</td>
                    <td class="px-4 py-3 font-mono text-sm font-medium text-slate-800">
                        {{ $sale->reference }}
                        @if($sale->booking_id)<br><span class="text-xs text-violet-600">Booking: {{ $sale->booking_id }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $sale->customer_name ?: ($sale->customer_phone ?: '–') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->items->sum('quantity') }} items</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800">£{{ number_format((float) $sale->total, 2) }}</td>
                    <td class="px-4 py-3 text-sm capitalize text-slate-600">{{ str_replace('_', ' ', $sale->payment_method ?? 'N/A') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.sales.receipt', $sale) }}" class="text-blue-600 hover:underline text-sm">Receipt</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">No sales in this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('sales-bulk-form');
        var selectAll = document.getElementById('sales-select-all');
        var checkboxes = form.querySelectorAll('.sales-row-cb');
        var deleteBtn = document.getElementById('sales-delete-btn');
        var countSpan = document.getElementById('sales-selected-count');
        function update() {
            var n = form.querySelectorAll('.sales-row-cb:checked').length;
            deleteBtn.disabled = n === 0;
            countSpan.textContent = n + ' selected';
            selectAll.checked = n > 0 && n === checkboxes.length;
        }
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            update();
        });
        checkboxes.forEach(function(cb) { cb.addEventListener('change', update); });
    });
    </script>
</x-admin-layout>
