<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <span>Bookings Calendar</span>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.attended') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-100 text-emerald-800 text-sm font-medium rounded-lg hover:bg-emerald-200">
                    Attended customers
                </a>
                <a href="{{ route('admin.bookings.canceled') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-300">
                    Canceled bookings
                </a>
                <button type="button" onclick="document.getElementById('create-booking-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Booking
                </button>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div id="bookings-calendar" class="w-full min-h-[400px] sm:min-h-[500px] md:min-h-[600px]" style="height: clamp(400px, 80vh, 600px);"
             data-events-url="{{ route('admin.bookings.list') }}"
             data-cancel-base="{{ url('/admin/bookings') }}"></div>
    </div>

    {{-- Create booking modal (for phone bookings / admin quick book) --}}
    <div id="create-booking-modal" class="fixed inset-0 z-50 {{ $openCreate ?? false ? '' : 'hidden' }}" aria-hidden="{{ $openCreate ?? false ? 'false' : 'true' }}">
        <div class="fixed inset-0 bg-slate-900/60" onclick="document.getElementById('create-booking-modal').classList.add('hidden')"></div>
        <div class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-xl p-4 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-bold text-slate-800">Quick Book</h3>
                <button type="button" onclick="document.getElementById('create-booking-modal').classList.add('hidden')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-700" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.bookings.store') }}" method="POST" class="space-y-3" onsubmit="return validateBookingForm(this)">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-1">Customer name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" required
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="John Smith">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-slate-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="tel" name="customer_phone" id="customer_phone" required
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="07895 859505">
                    </div>
                </div>
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-slate-700 mb-1">Email (optional)</label>
                    <input type="email" name="customer_email" id="customer_email"
                           class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="john@example.com">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="vehicle_registration" class="block text-sm font-medium text-slate-700 mb-1">Vehicle reg (VRN)</label>
                        <input type="text" name="vehicle_registration" id="vehicle_registration"
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="AB12 CDE">
                    </div>
                    <div>
                        <label for="vehicle_make" class="block text-sm font-medium text-slate-700 mb-1">Make</label>
                        <input type="text" name="vehicle_make" id="vehicle_make"
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Toyota">
                    </div>
                    <div>
                        <label for="vehicle_model" class="block text-sm font-medium text-slate-700 mb-1">Model</label>
                        <input type="text" name="vehicle_model" id="vehicle_model"
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Corolla">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="appointment_date" class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="appointment_date" id="appointment_date" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="appointment_time" class="block text-sm font-medium text-slate-700 mb-1">Time <span class="text-red-500">*</span></label>
                        <select name="appointment_time" id="appointment_time" required class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'] as $slot)
                            <option value="{{ $slot }}">{{ $slot }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Service <span class="text-red-500">*</span></label>
                        {{-- Hidden input carries the actual submitted value --}}
                        <input type="hidden" name="service_type" id="service_type_value" required>
                        <div class="relative" id="service-combobox">
                            <input type="text" id="service_search"
                                   autocomplete="off"
                                   placeholder="Search service…"
                                   class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8"
                                   oninput="filterServices(this.value)"
                                   onfocus="openServiceDropdown()"
                                   onblur="closeServiceDropdown()">
                            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                            </span>
                        </div>
                        {{-- Dropdown rendered outside modal so it isn't clipped by overflow-y-auto --}}
                        <ul id="service-dropdown"
                            style="display:none;position:fixed;z-index:9999;max-height:220px;overflow-y:auto;background:#fff;border:1px solid #e2e8f0;border-radius:0.5rem;box-shadow:0 10px 25px -5px rgba(0,0,0,.15);font-size:.875rem;">
                            @foreach($services ?? [] as $svc)
                            @php $p = (float)$svc->price; @endphp
                            <li class="service-option"
                                style="padding:.5rem .75rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:.5rem;"
                                data-value="{{ e($svc->title) }}"
                                data-price="{{ $p > 0 ? $p : '' }}"
                                data-label="{{ e($svc->title) }}{{ $p > 0 ? ' — £' . number_format($p, 2) : ' (Quote)' }}"
                                onmousedown="selectService(this)"
                                onmouseover="this.style.background='#eff6ff'"
                                onmouseout="this.style.background=''">
                                <span>{{ $svc->title }}</span>
                                <span style="color:#94a3b8;white-space:nowrap;font-size:.75rem;">{{ $p > 0 ? '£' . number_format($p, 2) : 'Quote' }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (£)</label>
                        <input type="number" name="total_amount" id="total_amount" step="0.01" min="0"
                               class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Create Booking
                    </button>
                    <button type="button" onclick="document.getElementById('create-booking-modal').classList.add('hidden')"
                            class="px-5 py-2.5 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // ── Service search combobox ──────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Move dropdown to body so modal overflow doesn't clip it
        const dd = document.getElementById('service-dropdown');
        if (dd) document.body.appendChild(dd);
    });

    function positionServiceDropdown() {
        const inp = document.getElementById('service_search');
        const dd  = document.getElementById('service-dropdown');
        if (!inp || !dd) return;
        const r = inp.getBoundingClientRect();
        dd.style.top   = (r.bottom + window.scrollY + 4) + 'px';
        dd.style.left  = (r.left  + window.scrollX) + 'px';
        dd.style.width = r.width + 'px';
    }

    function openServiceDropdown() {
        const dd = document.getElementById('service-dropdown');
        if (!dd) return;
        filterServices(document.getElementById('service_search').value);
        positionServiceDropdown();
        dd.style.display = 'block';
    }
    function closeServiceDropdown() {
        const dd = document.getElementById('service-dropdown');
        if (dd) dd.style.display = 'none';
    }
    function filterServices(query) {
        const q = query.toLowerCase();
        const dd = document.getElementById('service-dropdown');
        if (!dd) return;
        let hasVisible = false;
        dd.querySelectorAll('.service-option').forEach(li => {
            const match = li.dataset.label.toLowerCase().includes(q);
            li.style.display = match ? 'flex' : 'none';
            if (match) hasVisible = true;
        });
        dd.style.display = hasVisible ? 'block' : 'none';
    }
    function selectService(li) {
        document.getElementById('service_type_value').value = li.dataset.value;
        document.getElementById('service_search').value = li.dataset.label;
        const price = li.dataset.price ? parseFloat(li.dataset.price) : null;
        const amt = document.getElementById('total_amount');
        if (amt) amt.value = price !== null && !isNaN(price) ? price.toFixed(2) : '';
        closeServiceDropdown();
    }
    // Clear hidden value if user clears the search box
    document.addEventListener('input', function(e) {
        if (e.target.id === 'service_search' && e.target.value === '') {
            document.getElementById('service_type_value').value = '';
        }
    });
    // Reposition on scroll/resize
    window.addEventListener('scroll', positionServiceDropdown, true);
    window.addEventListener('resize', positionServiceDropdown);

    function validateBookingForm(form) {
        const val = document.getElementById('service_type_value').value;
        if (!val) {
            const inp = document.getElementById('service_search');
            inp.style.borderColor = '#ef4444';
            inp.placeholder = 'Please select a service';
            inp.focus();
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const openCreate = {{ ($openCreate ?? false) ? 'true' : 'false' }};
        if (openCreate) document.getElementById('create-booking-modal').classList.remove('hidden');
        const today = new Date().toISOString().slice(0, 10);
        const dateInput = document.getElementById('appointment_date');
        if (dateInput && !dateInput.value) dateInput.value = today;
    });
    </script>

    <!-- Booking details modal -->
    <div id="booking-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="fixed inset-0 bg-slate-900/60" onclick="document.getElementById('booking-modal').classList.add('hidden')"></div>
        <div class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Booking Details</h3>
                <button type="button" onclick="document.getElementById('booking-modal').classList.add('hidden')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-700" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <dl id="booking-modal-content" class="space-y-3 text-sm">
                <!-- populated by JS -->
            </dl>
            <div id="booking-modal-qr" class="mt-6 pt-4 border-t border-slate-200 hidden">
                <p class="text-sm font-medium text-slate-500 mb-2">Scan to call customer</p>
                <div id="booking-qr-canvas" class="inline-block p-2 bg-white rounded-lg border border-slate-200"></div>
            </div>
            <div id="booking-modal-actions" class="mt-6 pt-4 border-t border-slate-200 flex gap-3 flex-wrap items-center">
                <!-- Call/Email/Print buttons populated by JS -->
            </div>
        </div>
    </div>

    {{-- Mark as Attended confirmation modal (designed) --}}
    <div id="attend-confirm-modal" class="fixed inset-0 z-[60] hidden" aria-hidden="true" role="dialog" aria-labelledby="attend-modal-title" aria-modal="true" tabindex="-1">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" onclick="window.closeAttendConfirmModal()"></div>
        <div class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm mx-4 flex items-center justify-center min-h-[100px]">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200/80 w-full">
                <div class="p-6 sm:p-8 text-center">
                    <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mb-5 ring-4 ring-emerald-100">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 id="attend-modal-title" class="text-xl font-bold text-slate-800 mb-2">Mark as attended?</h3>
                    <p class="text-slate-600 text-[15px] leading-relaxed">A thank you email with a Google review link will be sent to the customer.</p>
                </div>
                <div class="px-6 pb-6 sm:px-8 sm:pb-8 flex gap-3">
                    <button type="button" onclick="window.closeAttendConfirmModal()"
                            class="flex-1 py-3 px-4 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors text-sm sm:text-base">
                        Cancel
                    </button>
                    <button type="button" onclick="window.confirmAttendSubmit()"
                            class="flex-1 py-3 px-4 rounded-xl font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/25 text-sm sm:text-base inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    #booking-modal #booking-modal-actions a { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none !important;border:none; }
    #booking-modal #booking-modal-actions a.btn-call { background-color:#16a34a !important; color:#fff !important; }
    #booking-modal #booking-modal-actions a.btn-call:hover { background-color:#15803d !important; color:#fff !important; }
    #booking-modal #booking-modal-actions a.btn-email { background-color:#475569 !important; color:#fff !important; }
    #booking-modal #booking-modal-actions a.btn-email:hover { background-color:#334155 !important; color:#fff !important; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</x-admin-layout>
