/**
 * Bookings calendar - uses FullCalendar from CDN (no Vite/npm build required)
 */
(function () {
    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function initBookingsCalendar() {
        var calendarEl = document.getElementById('bookings-calendar');
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        var eventsUrl = calendarEl.dataset.eventsUrl;
        var cancelBase = calendarEl.dataset.cancelBase || '';
        window._bookingInvoiceBase = cancelBase;
        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        if (!eventsUrl) {
            calendarEl.innerHTML = '<p class="text-slate-500 p-4">Missing configuration. Please refresh.</p>';
            return;
        }

        try {
            var Calendar = FullCalendar.Calendar;
            var dayGridPlugin = FullCalendar.dayGridPlugin;
            var timeGridPlugin = FullCalendar.timeGridPlugin;
            var listPlugin = FullCalendar.listPlugin;
            var interactionPlugin = FullCalendar.interactionPlugin;

            var calendar = new Calendar(calendarEl, {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: function (info, successCallback, failureCallback) {
                    fetch(eventsUrl + '?start=' + encodeURIComponent(info.startStr) + '&end=' + encodeURIComponent(info.endStr))
                        .then(function (r) { return r.json(); })
                        .then(function (events) { successCallback(events); })
                        .catch(function () { failureCallback(); });
                },
                eventClick: function (info) {
                    var p = info.event.extendedProps;
                    var start = info.event.start;
                    var dateStr = start ? start.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) : '';
                    var timeStr = start ? start.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '';
                    var items = [
                        { label: 'Booking ID', value: p.bookingId },
                        { label: 'Date & Time', value: dateStr && timeStr ? dateStr + ' at ' + timeStr : null },
                        { label: 'Customer', value: p.customer },
                        { label: 'Phone', value: p.phone, type: 'tel' },
                        { label: 'Email', value: p.email, type: 'mailto' },
                        { label: 'Vehicle', value: p.vehicle },
                        { label: 'Service', value: p.service },
                        { label: 'Amount', value: p.amount }
                    ];
                    var content = document.getElementById('booking-modal-content');
                    if (content) {
                        content.innerHTML = items.filter(function (i) { return i.value; }).map(function (i) {
                            var val = escapeHtml(String(i.value));
                            if (i.type === 'tel') val = '<a href="tel:' + escapeHtml(String(i.value).replace(/\s/g, '')) + '" class="text-blue-600 hover:underline">' + val + '</a>';
                            if (i.type === 'mailto') val = '<a href="mailto:' + escapeHtml(i.value) + '" class="text-blue-600 hover:underline">' + val + '</a>';
                            return '<div class="flex gap-3 py-1"><dt class="font-medium text-slate-500 w-28 shrink-0">' + i.label + '</dt><dd class="text-slate-800">' + val + '</dd></div>';
                        }).join('');
                        if (content.innerHTML === '') content.innerHTML = '<p class="text-slate-500">No details available.</p>';
                    }
                    var qrContainer = document.getElementById('booking-modal-qr');
                    var qrCanvas = document.getElementById('booking-qr-canvas');
                    if (qrCanvas) qrCanvas.innerHTML = '';
                    if (qrContainer) {
                        if (p.phone && typeof QRCode !== 'undefined') {
                            var digits = String(p.phone).replace(/\D/g, '');
                            var tel = digits.charAt(0) === '0' ? 'tel:+44' + digits.slice(1) : (digits.length >= 10 ? 'tel:+44' + digits.replace(/^44/, '') : 'tel:' + digits);
                            if (!tel.startsWith('tel:+') && digits.length === 11) tel = 'tel:+44' + digits.slice(1);
                            try {
                                new QRCode(qrCanvas, { text: tel, width: 140, height: 140 });
                                qrContainer.classList.remove('hidden');
                            } catch (e) {
                                qrContainer.classList.add('hidden');
                            }
                        } else {
                            qrContainer.classList.add('hidden');
                        }
                    }
                    var actions = document.getElementById('booking-modal-actions');
                    if (actions) {
                        var btnHtml = '';
                        if (p.phone) btnHtml += '<a class="btn-call" href="tel:' + escapeHtml(String(p.phone).replace(/\s/g, '')) + '" style="background-color:#16a34a;color:#fff;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;border:none"><svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>Call</a>';
                        if (p.email) btnHtml += '<a class="btn-email" href="mailto:' + escapeHtml(p.email) + '" style="background-color:#475569;color:#fff;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;border:none">Email</a>';
                        btnHtml += '<button type="button" onclick="window.printInvoice()" class="btn-print" style="background-color:#1B263B;color:#fff;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;border:none;cursor:pointer"><svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Print Invoice</button>';
                        btnHtml += '<form method="POST" action="' + cancelBase + '/' + info.event.id + '/cancel" class="inline" onsubmit="return confirm(\'Cancel this booking? It will be removed from the calendar and listed under Canceled bookings.\')"><input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '"><button type="submit" style="background-color:#dc2626;color:#fff;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;border:none;cursor:pointer">Cancel booking</button></form>';
                        btnHtml += '<button type="button" onclick="document.getElementById(\'booking-modal\').classList.add(\'hidden\')" style="background-color:#e2e8f0;color:#475569;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;border:none;cursor:pointer">Close</button>';
                        actions.innerHTML = btnHtml || '';
                    }
                    window._currentBookingPrint = { p: p, dateStr: dateStr, timeStr: timeStr };
                    window._currentBookingId = info.event.id;
                    var modal = document.getElementById('booking-modal');
                    if (modal) modal.classList.remove('hidden');
                }
            });
            calendar.render();
        } catch (e) {
            console.error('Calendar init error:', e);
            calendarEl.innerHTML = '<p class="text-red-500 p-4">Calendar failed to load. Make sure FullCalendar scripts are loaded.</p>';
        }
    }

    window.printInvoice = function () {
        var id = window._currentBookingId;
        var base = window._bookingInvoiceBase || '';
        if (!id || !base) return;
        window.open(base + '/' + id + '/invoice?print=1', '_blank', 'noopener');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBookingsCalendar);
    } else {
        initBookingsCalendar();
    }
})();
