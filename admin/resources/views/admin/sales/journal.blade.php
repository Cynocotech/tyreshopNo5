<x-admin-layout>
    <x-slot name="header">Sales Journal</x-slot>

    @include('admin.sales._filters')

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.index', request()->query()) }}" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-medium">Sales list</a>
        <a href="{{ route('admin.sales.journal', request()->query()) }}" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm font-medium">Journal</a>
        <a href="{{ route('admin.sales.daily', request()->query()) }}" class="px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-medium">Daily summary</a>
        <a href="{{ route('admin.sales.export', request()->query()) }}" class="px-3 py-1.5 rounded bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium">Export CSV</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-800">Sales Journal (accounting ledger)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Chronological record of all sales for bookkeeping and audit trail.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Debit (Revenue)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Payment</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                            {{ $sale->completed_at?->format('d/m/Y H:i') ?? '–' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-mono font-medium text-slate-800">{{ $sale->reference }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ $sale->customer_name ?: 'Walk-in' }}
                            @if($sale->customer_vrn)<span class="text-slate-500">({{ $sale->customer_vrn }})</span>@endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            @foreach($sale->items as $item)
                                {{ $item->product?->name ?? 'Product' }} × {{ $item->quantity }}@if(!$loop->last), @endif
                            @endforeach
                            @if($sale->items->isEmpty())–@endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-700">£{{ number_format((float) $sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 capitalize">{{ str_replace('_', ' ', $sale->payment_method ?? '') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.sales.receipt', $sale) }}" class="text-blue-600 hover:underline text-sm">Receipt</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($sales->isEmpty())
        <div class="px-6 py-12 text-center text-slate-500">No sales in this date range.</div>
        @else
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-wrap gap-6 text-sm">
            <span><strong>Period total:</strong> £{{ number_format($summary['grand_total'], 2) }}</span>
            <span><strong>Transactions:</strong> {{ $summary['transaction_count'] }}</span>
            @foreach($summary['by_method'] as $method => $row)
            <span class="capitalize">{{ str_replace('_', ' ', $method) }}: £{{ number_format((float) $row->total, 2) }} ({{ $row->count }})</span>
            @endforeach
        </div>
        @endif
    </div>
</x-admin-layout>
