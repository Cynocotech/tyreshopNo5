@props(['from', 'to'])

<div class="mb-6 flex flex-wrap items-center gap-4">
    <div class="flex gap-2 border-b border-slate-200">
        <a href="{{ route('admin.sales.index', ['from' => $from, 'to' => $to]) }}"
           class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.sales.index') ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-800' }}">
            Sales list
        </a>
        <a href="{{ route('admin.sales.journal', ['from' => $from, 'to' => $to]) }}"
           class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.sales.journal') ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-800' }}">
            Sales journal
        </a>
        <a href="{{ route('admin.sales.daily', ['from' => $from, 'to' => $to]) }}"
           class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.sales.daily') ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-800' }}">
            Daily summary
        </a>
    </div>
    <a href="{{ route('admin.sales.export', ['from' => $from, 'to' => $to]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
</div>
