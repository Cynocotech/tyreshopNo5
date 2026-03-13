<form method="GET" action="{{ url()->current() }}" class="mb-4 flex flex-wrap items-center gap-3">
    <label class="text-sm font-medium text-slate-700">From</label>
    <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-slate-300 text-sm py-1.5">
    <label class="text-sm font-medium text-slate-700">To</label>
    <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-slate-300 text-sm py-1.5">
    <button type="submit" class="px-3 py-1.5 bg-slate-700 text-white rounded-lg text-sm font-medium hover:bg-slate-800">Apply</button>
</form>
