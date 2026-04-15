<x-admin-layout>
    <x-slot name="header">Services</x-slot>

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Service</a>
        <div class="flex items-center gap-3">
            <span id="reorder-status" class="text-sm text-slate-400 hidden">Saving…</span>
            <form id="bulk-delete-form" action="{{ route('admin.services.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete all selected services? This cannot be undone.')">
                @csrf
                <button id="bulk-delete-btn" type="submit" disabled class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">Delete Selected</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form id="service-select-form" action="{{ route('admin.services.bulk-delete') }}" method="POST">
            @csrf
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-3 w-8"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">
                        <input id="select-all-services" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" aria-label="Select all services" />
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Icon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Title</th>
                    <th id="sort-category-th" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase cursor-pointer select-none hover:text-blue-600" title="Click to sort by category">
                        Category <span id="sort-indicator" class="ml-1 text-slate-400">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Price</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-services" class="divide-y divide-slate-200">
                @forelse($services as $s)
                <tr data-id="{{ $s->id }}" class="hover:bg-slate-50 transition-colors">
                    <td class="px-3 py-3 cursor-grab text-slate-300 hover:text-slate-500 select-none drag-handle" title="Drag to reorder">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><circle cx="5" cy="4" r="1.3"/><circle cx="11" cy="4" r="1.3"/><circle cx="5" cy="8" r="1.3"/><circle cx="11" cy="8" r="1.3"/><circle cx="5" cy="12" r="1.3"/><circle cx="11" cy="12" r="1.3"/></svg>
                    </td>
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
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No services yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </form>
    </div>

    <p class="mt-2 text-xs text-slate-400">Drag the ⠿ handle to reorder. Order is saved automatically.</p>

    <script>
    (function () {
        // ── Checkbox select-all ──
        var selectAll = document.getElementById('select-all-services');
        var bulkBtn   = document.getElementById('bulk-delete-btn');
        var boxes     = Array.prototype.slice.call(document.querySelectorAll('.service-select'));

        function updateCheckboxUi() {
            var n = boxes.filter(function (b) { return b.checked; }).length;
            bulkBtn.disabled = n === 0;
            selectAll.checked       = n > 0 && n === boxes.length;
            selectAll.indeterminate = n > 0 && n < boxes.length;
        }
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                boxes.forEach(function (b) { b.checked = selectAll.checked; });
                updateCheckboxUi();
            });
            boxes.forEach(function (b) { b.addEventListener('change', updateCheckboxUi); });
            updateCheckboxUi();
        }

        // ── Shared ──
        var tbody = document.getElementById('sortable-services');

        function getRows() {
            return Array.prototype.slice.call(tbody ? tbody.querySelectorAll('tr[data-id]') : []);
        }

        // ── Sort by category ──
        var sortTh        = document.getElementById('sort-category-th');
        var sortIndicator = document.getElementById('sort-indicator');
        var sortDir       = 0; // 0=default, 1=asc, -1=desc

        if (sortTh && tbody) {
            sortTh.addEventListener('click', function () {
                sortDir = sortDir === 1 ? -1 : 1;
                sortIndicator.textContent = sortDir === 1 ? '↑' : '↓';
                sortIndicator.classList.toggle('text-blue-600', true);
                sortIndicator.classList.remove('text-slate-400');

                var rows = getRows();
                rows.sort(function (a, b) {
                    var ca = (a.querySelector('td:nth-child(6)') || a.cells[5] || {}).textContent.trim().toLowerCase();
                    var cb = (b.querySelector('td:nth-child(6)') || b.cells[5] || {}).textContent.trim().toLowerCase();
                    if (ca < cb) return -1 * sortDir;
                    if (ca > cb) return  1 * sortDir;
                    return 0;
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        }

        // ── Drag-and-drop reorder ──
        var status   = document.getElementById('reorder-status');
        var dragSrc  = null;
        var saveTimer = null;

        if (!tbody) return;

        function saveOrder() {
            var ids = getRows().map(function (r) { return parseInt(r.getAttribute('data-id'), 10); });
            status.textContent = 'Saving…';
            status.classList.remove('hidden', 'text-green-600', 'text-red-500');
            status.classList.add('text-slate-400');

            fetch('{{ route('admin.services.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ?
                        document.querySelector('meta[name=csrf-token]').content :
                        '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.ok) {
                    status.textContent = 'Order saved ✓';
                    status.classList.remove('text-slate-400', 'text-red-500');
                    status.classList.add('text-green-600');
                } else {
                    throw new Error('Server error');
                }
                clearTimeout(saveTimer);
                saveTimer = setTimeout(function () { status.classList.add('hidden'); }, 2500);
            })
            .catch(function () {
                status.textContent = 'Save failed — try again';
                status.classList.remove('text-slate-400', 'text-green-600');
                status.classList.add('text-red-500');
            });
        }

        getRows().forEach(function (row) {
            // Only start drag from the handle
            var handle = row.querySelector('.drag-handle');
            if (handle) {
                handle.addEventListener('mousedown', function () { row.draggable = true; });
                handle.addEventListener('mouseleave', function () {
                    if (!dragSrc) row.draggable = false;
                });
            }

            row.addEventListener('dragstart', function (e) {
                dragSrc = row;
                e.dataTransfer.effectAllowed = 'move';
                row.style.opacity = '0.4';
            });

            row.addEventListener('dragend', function () {
                row.style.opacity = '';
                row.draggable = false;
                dragSrc = null;
                getRows().forEach(function (r) { r.classList.remove('drag-over'); });
                saveOrder();
            });

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                if (row !== dragSrc) {
                    getRows().forEach(function (r) { r.classList.remove('drag-over'); });
                    row.classList.add('drag-over');
                }
            });

            row.addEventListener('drop', function (e) {
                e.preventDefault();
                if (!dragSrc || dragSrc === row) return;
                var rows = getRows();
                var srcIdx  = rows.indexOf(dragSrc);
                var destIdx = rows.indexOf(row);
                if (srcIdx < destIdx) {
                    tbody.insertBefore(dragSrc, row.nextSibling);
                } else {
                    tbody.insertBefore(dragSrc, row);
                }
                row.classList.remove('drag-over');
            });
        });
    })();
    </script>

    <style>
        tr.drag-over { background: #eff6ff; outline: 2px dashed #93c5fd; }
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
    </style>
</x-admin-layout>
