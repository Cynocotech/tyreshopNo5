<x-admin-layout>
    <x-slot name="header">Inventory Report</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-slate-800">Stock & sales by period</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.index', ['period' => 'week']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ ($period ?? '') === 'week' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                This week
            </a>
            <a href="{{ route('admin.inventory.index', ['period' => 'last_week']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ ($period ?? '') === 'last_week' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                Last week
            </a>
            <a href="{{ route('admin.inventory.index', ['period' => 'month']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ ($period ?? '') === 'month' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                This month
            </a>
            <a href="{{ route('admin.inventory.index', ['period' => 'last_month']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ ($period ?? '') === 'last_month' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                Last month
            </a>
        </div>
    </div>

    <p class="text-sm text-slate-600 mb-4">
        Showing: <strong>{{ $periodLabel }}</strong>
        ({{ $start->format('d M Y') }} – {{ $end->format('d M Y') }})
    </p>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Tyre size</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Current stock</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Sold (period)</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-600 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($products as $p)
                <tr class="{{ $p->low_stock ? 'bg-amber-50' : '' }}">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $p->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $p->category ?? '–' }}</td>
                    <td class="px-4 py-3 text-slate-600 font-mono text-sm">{{ $p->tyre_size ?? '–' }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ $p->stock }}</td>
                    <td class="px-4 py-3 text-right text-emerald-600 font-medium">{{ $p->sold }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($p->low_stock)
                            <span class="text-xs font-medium text-amber-600 px-2 py-1 rounded bg-amber-100">Low stock</span>
                        @else
                            <span class="text-slate-400">–</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No products.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
