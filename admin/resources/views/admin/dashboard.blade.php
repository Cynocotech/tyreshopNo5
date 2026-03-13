<x-admin-layout>
    <x-slot name="header">Dashboard</x-slot>

    @if($lowStockProducts->isNotEmpty())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 flex gap-4">
        <div class="shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-amber-800">Low stock alert</h3>
            <p class="text-sm text-amber-700 mt-0.5">Products running low: @foreach($lowStockProducts as $p)<a href="{{ route('admin.products.edit', $p) }}" class="hover:underline font-medium">{{ $p->name }}</a>@if(!$loop->last), @endif @endforeach</p>
        </div>
    </div>
    @endif

    {{-- Top row: Key metrics with circular progress (Shopzy style) --}}
    @php
        $maxOrders = max($ordersLast90 ?? 0, 100);
        $ordersPct = $maxOrders > 0 ? min(100, ($ordersLast90 ?? 0) / $maxOrders * 100) : 0;
        $servicesPct = max(0, min(100, ($servicesCount ?? 0) * 10));
        $revenuePct = ($revenueLast90 ?? 0) > 0 ? min(100, 50 + (($salesRevenueMonth ?? 0) / ($revenueLast90 / 3)) * 20) : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.sales.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 shrink-0">
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ $ordersPct }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700">{{ $ordersLast90 ?? 0 }}</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $ordersLast90 ?? 0 }}</p>
                    <p class="text-sm text-slate-500">Orders (Last 90 Days)</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.sales.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 shrink-0">
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ min(100, ($salesCountMonth ?? 0) * 5) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700">{{ $salesCountMonth ?? 0 }}</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">£{{ number_format($salesRevenueMonth ?? 0, 2) }}</p>
                    <p class="text-sm text-slate-500">Revenue this month</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.services.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 shrink-0">
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-violet-500" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ $servicesPct }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700">{{ $servicesCount ?? 0 }}</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $servicesCount ?? 0 }}</p>
                    <p class="text-sm text-slate-500">Services</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.products.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 shrink-0">
                    @php $lowStockPct = min(100, ($lowStockProducts->count() ?? 0) * 20); @endphp
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-amber-500" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ $lowStockPct }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700">{{ $lowStockProducts->count() ?? 0 }}</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $lowStockProducts->count() ?? 0 }}</p>
                    <p class="text-sm text-slate-500">Low stock items</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Revenue Report (bar chart) + Recent Sales + Right widgets --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Revenue + Recent --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Revenue Report --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Revenue Report</h2>
                    <span class="text-sm text-slate-500">{{ now()->format('F Y') }}</span>
                </div>
                <div class="p-6">
                    @php $maxRev = $monthlyRevenue->max() ?: 1; @endphp
                    <div class="flex items-end gap-2 h-40">
                        @foreach($monthlyRevenue ?? collect() as $month => $total)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-blue-100 rounded-t" style="height: {{ ($total / $maxRev) * 120 }}px; min-height: {{ $total > 0 ? 8 : 0 }}px;"></div>
                            <span class="text-xs text-slate-500 truncate w-full text-center">{{ \Carbon\Carbon::parse($month . '-01')->format('M') }}</span>
                        </div>
                        @endforeach
                        @if(($monthlyRevenue ?? collect())->isEmpty())
                        <p class="text-slate-500 text-sm py-8 col-span-full text-center">No revenue data yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent order --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Recent orders</h2>
                    <a href="{{ route('admin.sales.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">See All</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentSales ?? [] as $sale)
                    <a href="{{ route('admin.sales.receipt', $sale) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-800 truncate">{{ $sale->items->map(fn($i) => ($i->product->name ?? 'Product') . ' × ' . $i->quantity)->take(2)->join(', ') }}{{ $sale->items->count() > 2 ? '…' : '' }}</p>
                            <p class="text-xs text-slate-500">{{ $sale->completed_at?->diffForHumans() ?? '–' }}</p>
                        </div>
                        <p class="font-semibold text-slate-800 shrink-0">£{{ number_format((float)$sale->total, 2) }}</p>
                    </a>
                    @empty
                    <div class="px-6 py-12 text-center text-slate-500">No recent sales.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Widgets --}}
        <div class="space-y-6">
            {{-- Today Best Sales --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">Today Best Sales</h3>
                @if($todayBest ?? null)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">{{ $todayBest->reference }}</p>
                        <p class="text-lg font-bold text-emerald-600">£{{ number_format((float)$todayBest->total, 2) }}</p>
                    </div>
                </div>
                @else
                <p class="text-slate-500 text-sm py-2">No sales today yet.</p>
                @endif
            </div>

            {{-- Latest Customers --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-800">Latest customers</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($latestCustomers ?? [] as $cust)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <p class="font-medium text-slate-800 truncate">{{ $cust->customer_name }}</p>
                        <span class="text-xs text-slate-500 shrink-0 ml-2">{{ $cust->purchases ?? 1 }} purchases</span>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-slate-500 text-sm">No named customers yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Trending items --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800">Trending items</h3>
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">See All</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($topProducts ?? [] as $item)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <p class="font-medium text-slate-800 truncate">{{ $item->product?->name ?? 'Product' }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 shrink-0 ml-2">{{ $item->total_qty }} sold</span>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-slate-500 text-sm">No sales yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Book (for phone-in customers) --}}
            <a href="{{ route('admin.bookings.index', ['new' => 1]) }}" class="block bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-200 transition-all group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Quick Book</h3>
                </div>
                <p class="text-xs text-slate-500 mb-3">Book a customer when they call — all services available.</p>
                <span class="inline-flex items-center gap-2 py-2.5 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg group-hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Booking
                </span>
            </a>

            {{-- Export --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Export to Site</h3>
                </div>
                <p class="text-xs text-slate-500 mb-3">Push services & categories to your live site.</p>
                <form action="{{ route('admin.export') }}" method="GET">
                    <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Export now</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
