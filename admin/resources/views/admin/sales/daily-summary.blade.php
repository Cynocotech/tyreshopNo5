<x-admin-layout>
    <x-slot name="header">Daily Summary</x-slot>

    @include('admin.sales._filters')

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.index', request()->query()) }}" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-medium">Sales list</a>
        <a href="{{ route('admin.sales.journal', request()->query()) }}" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-medium">Journal</a>
        <a href="{{ route('admin.sales.daily', request()->query()) }}" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm font-medium">Daily summary</a>
        <a href="{{ route('admin.sales.export', request()->query()) }}" class="px-3 py-1.5 rounded bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium">Export CSV</a>
    </div>

    {{-- Period summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-slate-500 text-sm font-medium">Period Total</h3>
            <p class="text-2xl font-bold text-slate-800">£{{ number_format($grandTotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-slate-500 text-sm font-medium">Transactions</h3>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($totalTransactions) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-slate-500 text-sm font-medium">Avg per Sale</h3>
            <p class="text-2xl font-bold text-slate-800">£{{ $totalTransactions > 0 ? number_format($grandTotal / $totalTransactions, 2) : '0.00' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-slate-500 text-sm font-medium">Days with Sales</h3>
            <p class="text-2xl font-bold text-slate-800">{{ $daily->count() }}</p>
        </div>
    </div>

    {{-- Payment method breakdown --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-800">Payment method breakdown</h2>
            <p class="text-xs text-slate-500 mt-0.5">Totals by payment type for bank reconciliation.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Payment method</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Total (£)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach(['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'] as $key => $label)
                    @php $row = $byPayment->get($key); @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800 capitalize">{{ $label }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $row ? 'text-emerald-700' : 'text-slate-400' }}">£{{ $row ? number_format((float) $row->total, 2) : '0.00' }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $row ? $row->count : 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily breakdown with bulk delete --}}
    <form method="POST" action="{{ route('admin.sales.bulk-delete-by-dates') }}" id="daily-bulk-form" onsubmit="return confirm('Delete ALL sales from selected dates? This cannot be undone.');">
        @csrf
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <div class="mb-3 flex items-center gap-3">
            <button type="submit" id="daily-delete-btn" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">Delete selected days</button>
            <span class="text-sm text-slate-500" id="daily-selected-count">0 selected</span>
        </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-800">Daily totals</h2>
            <p class="text-xs text-slate-500 mt-0.5">Cash-up report — total sales per day. Select dates to delete all sales from those days.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" id="daily-select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select all">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Transactions</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Total (£)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($daily as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="dates[]" value="{{ $row->sale_date }}" class="daily-row-cb rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ \Carbon\Carbon::parse($row->sale_date)->format('l, j M Y') }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $row->count }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">£{{ number_format((float) $row->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">No sales in this date range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('daily-bulk-form');
        if (!form) return;
        var selectAll = document.getElementById('daily-select-all');
        var checkboxes = form.querySelectorAll('.daily-row-cb');
        var deleteBtn = document.getElementById('daily-delete-btn');
        var countSpan = document.getElementById('daily-selected-count');
        function update() {
            var n = form.querySelectorAll('.daily-row-cb:checked').length;
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
