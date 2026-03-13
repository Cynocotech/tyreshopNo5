<x-admin-layout>
    <x-slot name="header">EPOS</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="eposApp({{ json_encode($cardTerminalConfig ?? []) }})">
        {{-- Barcode / Scanner input --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    {{-- Barcode icon --}}
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 4h1v16H2V4zm3 0h1v16H5V4zm2 0h.5v16H7V4zm2 0h2v16H9V4zm3 0h1v16h-1V4zm3 0h.5v16H15V4zm2 0h1v16h-1V4zm3 0h2v16h-2V4z"/>
                    </svg>
                    {{-- Bluetooth icon --}}
                    <svg class="w-5 h-5 text-slate-500" fill="currentColor" viewBox="0 0 24 24" title="Bluetooth" aria-hidden="true">
                        <path d="M14.71 9.29L11 12.58V5h1v3.58l3.29-3.29.71.71-2.29 2.29 2.29 2.29-.71.71L11 13.41v3.58h-1v-3.58l-3.29 3.29-.71-.71 2.29-2.29-2.29-2.29.71-.71L10 10.59V7h1v2.59l3.29-3.29.71.71zM12 2c.55 0 1 .45 1 1v1h-2V3c0-.55.45-1 1-1zm0 18c-.55 0-1-.45-1-1v-1h2v1c0 .55-.45 1-1 1z"/>
                    </svg>
                    {{-- USB/wire icon --}}
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Wired/USB" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <label class="text-sm font-medium text-slate-700">Scan or enter barcode</label>
                </div>
                <p class="text-xs text-slate-500 mb-2 flex items-center gap-1.5">
                    <span>Bluetooth or wired barcode scanners act as keyboards</span>
                    <span class="inline-flex items-center gap-0.5" title="Both connection types supported">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14.5 12c0-1.5-.75-2.75-2-3.5l1-1.75c2 1.25 3.25 3.5 3.25 5.25s-1.25 4-3.25 5.25l-1-1.75c1.25-.75 2-2 2-3.5z"/><path d="M12 9.5c-1.4 0-2.5 1.1-2.5 2.5s1.1 2.5 2.5 2.5 2.5-1.1 2.5-2.5-1.1-2.5-2.5-2.5z"/><path d="M9.5 12c0-1.5.75-2.75 2-3.5l-1-1.75c-2 1.25-3.25 3.5-3.25 5.25s1.25 4 3.25 5.25l1-1.75c-1.25-.75-2-2-2-3.5z"/></svg>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H5a2 2 0 00-2 2v12a2 2 0 002 2h6m0 0h6a2 2 0 002-2V6a2 2 0 00-2-2h-6"/></svg>
                    </span>
                    — focus here and scan.
                </p>
                <input type="text" x-ref="barcodeInput" x-model="barcodeInput" @keydown.enter.prevent="lookupAndAdd()"
                       placeholder="Scan product barcode or type and press Enter"
                       class="w-full rounded-lg border-slate-300 py-3 px-4 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pl-12"
                       autofocus>
            </div>
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <label class="text-sm font-medium text-slate-700">Booking ID</label>
                </div>
                <p class="text-xs text-slate-500 mb-2">Enter booking ID (e.g. N05-1234567890-abc1) to load their invoice and pay.</p>
                <div class="flex gap-2">
                    <input type="text" x-model="bookingIdInput" @keydown.enter.prevent="loadBooking()"
                           placeholder="N05-1234567890-abc1"
                           class="flex-1 rounded-lg border-slate-300 py-2 px-4 focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    <button type="button" @click="loadBooking()"
                            class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700">
                        Load
                    </button>
                </div>
                <p class="text-xs text-red-600 mt-1" x-show="bookingError" x-text="bookingError"></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 mb-4" x-show="hasSerialItems">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    <label class="text-sm font-medium text-slate-700">Scan serial number</label>
                </div>
                <p class="text-xs text-slate-500 mb-2">Use scanner or type serial — works with keyboard-mode scanners.</p>
                <input type="text" x-ref="serialInput" x-model="serialInput" @keydown.enter.prevent="assignSerial()"
                       placeholder="Scan serial and press Enter"
                       class="w-full rounded-lg border-slate-300 py-2 px-4 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 pl-10">
            </div>

            {{-- Product grid --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-medium text-slate-700 mb-3">Products — click to add</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2" id="epos-product-grid">
                    @foreach($products as $p)
                    <button type="button" @click="addProduct({{ json_encode([
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => (float) $p->price,
                        'requires_serial' => $p->requires_serial,
                        'icon' => $p->icon ?: 'package',
                        'available_serials' => $p->requires_serial ? $p->availableSerials->pluck('serial_number')->values() : [],
                    ]) }})"
                            class="text-left p-3 rounded-lg bg-blue-50 border-2 border-blue-200 hover:border-blue-500 hover:bg-blue-100 transition flex flex-col items-center gap-2">
                        <x-product-icon :icon="$p->icon ?: 'package'" class="w-10 h-10 text-blue-600 shrink-0" />
                        <div class="w-full text-center">
                            <div class="font-medium text-slate-800 truncate">{{ $p->name }}</div>
                            <div class="text-sm text-slate-600">£{{ number_format($p->price, 2) }}</div>
                            @if($p->requires_serial)
                            <div class="text-xs text-amber-600 mt-1">Serial required</div>
                            @endif
                        </div>
                    </button>
                    @endforeach
                    @if($products->isEmpty())
                    <div class="col-span-full text-slate-500 py-4 text-center">No products. <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:underline">Add products</a> first.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cart --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-4 sticky top-4">
                <h3 class="text-sm font-medium text-slate-700 mb-3">Cart</h3>
                <template x-if="cart.length === 0">
                    <p class="text-slate-500 text-sm py-4">Cart is empty. Scan or click a product.</p>
                </template>
                <ul class="space-y-2" x-show="cart.length > 0">
                    <template x-for="(item, idx) in cart" :key="item.tempId">
                        <li class="flex flex-col gap-1 py-2 border-b border-slate-100 last:border-0">
                            <div class="flex justify-between items-start">
                                <span class="font-medium text-slate-800" x-text="item.name"></span>
                                <button type="button" @click="removeFromCart(idx)" class="px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium">Remove</button>
                            </div>
                            <div class="flex justify-between items-center text-sm text-slate-600 gap-2">
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" @click="item.quantity = Math.max(1, (parseInt(item.quantity, 10) || 1) - 1)" class="w-7 h-7 rounded border border-slate-300 bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-700 font-bold" title="Decrease">−</button>
                                    <input type="number" min="1" step="1"
                                           :value="item.quantity"
                                           @input="const v = $event.target.value; item.quantity = v === '' ? '' : Math.max(1, parseInt(v, 10) || 1)"
                                           @blur="item.quantity = Math.max(1, parseInt(item.quantity, 10) || 1)"
                                           class="w-12 text-center rounded border-slate-300 text-sm py-1">
                                    <button type="button" @click="item.quantity = Math.max(1, (parseInt(item.quantity, 10) || 1) + 1)" class="w-7 h-7 rounded border border-slate-300 bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-700 font-bold" title="Increase">+</button>
                                </div>
                                <span>£<span x-text="(item.unit_price * (item.quantity || 1)).toFixed(2)"></span></span>
                            </div>
                            <template x-if="item.requires_serial">
                                <div>
                                    <input type="text" :placeholder="'Serial number'" x-model="item.serial_number"
                                           class="mt-1 w-full rounded border-slate-300 text-sm py-1 px-2"
                                           :list="'serials-'+item.tempId">
                                    <datalist :id="'serials-'+item.tempId">
                                        <template x-for="sn in (item.available_serials || [])" :key="sn">
                                            <option :value="sn"></option>
                                        </template>
                                    </datalist>
                                </div>
                            </template>
                        </li>
                    </template>
                </ul>

                <div class="mt-4 pt-4 border-t border-slate-200" x-show="cart.length > 0">
                    <div class="flex justify-between font-semibold text-slate-800 mb-3">
                        <span>Total</span>
                        <span>£<span x-text="total.toFixed(2)"></span></span>
                    </div>
                    <button type="button" id="epos-checkout-btn" @click="openCheckout()"
                            class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md">
                        Checkout
                    </button>
                    <p class="text-xs text-amber-600 mt-2" x-show="completeError" x-text="completeError"></p>
                </div>
            </div>
        </div>

        {{-- Checkout / Paid Modal (must be inside x-data scope) --}}
        <div x-show="checkoutOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="paidSale ? (paidSale = null, checkoutOpen = false) : (checkoutOpen = false)">
        <div class="rounded-xl shadow-2xl max-w-md w-full overflow-hidden bg-white border border-slate-200"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            {{-- Paid success view --}}
            <div x-show="paidSale" x-cloak class="p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-emerald-100 flex items-center justify-center shadow-inner">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-1">Payment successful</h3>
                <p class="text-slate-500 text-sm mb-6">Sale completed</p>
                <div class="bg-slate-50 rounded-xl border border-emerald-200 p-4 mb-6 text-left space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Reference</span>
                        <span class="font-mono font-semibold text-slate-800" x-text="paidSale?.reference"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Amount paid</span>
                        <span class="font-bold text-emerald-700">£<span x-text="paidSale?.total?.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Method</span>
                        <span class="font-medium capitalize" x-text="(paidSale?.payment_method || '').replace('_', ' ')"></span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <a :href="paidSale ? '{{ url('/admin/sales') }}/' + paidSale.saleId + '/receipt' : '#'"
                       target="_self" class="block w-full py-3 rounded-xl bg-slate-800 text-white font-semibold hover:bg-slate-900 transition shadow-md text-center">
                        View receipt
                    </a>
                    <button type="button" @click="paidSale = null; checkoutOpen = false"
                            class="w-full py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition shadow-md">
                        Done — next sale
                    </button>
                </div>
            </div>

            {{-- Checkout form view --}}
            <div x-show="!paidSale" class="max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Checkout</h3>

                {{-- Order summary --}}
                <div class="mb-4 pb-4 border-b border-slate-200">
                    <div class="space-y-1 max-h-32 overflow-y-auto text-sm">
                        <template x-for="(item, idx) in cart" :key="item.tempId">
                            <div class="flex justify-between">
                                <span x-text="item.name + ' × ' + (parseInt(item.quantity, 10) || 1)"></span>
                                <span>£<span x-text="(item.unit_price * (parseInt(item.quantity, 10) || 1)).toFixed(2)"></span></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between font-semibold mt-2 pt-2 border-t border-slate-100">
                        <span>Total</span>
                        <span>£<span x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>

                {{-- Customer details (optional) --}}
                <div class="mb-4 pb-4 border-b border-slate-200">
                    <h4 class="text-sm font-medium text-slate-700 mb-2">Customer details <span class="text-slate-400 font-normal">(optional)</span></h4>
                    <div class="space-y-2">
                        <input type="text" x-model="customerName" placeholder="Name"
                               class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="email" x-model="customerEmail" placeholder="Email"
                               class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="tel" x-model="customerPhone" placeholder="Phone"
                               class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="text" x-model="customerVrn" placeholder="VRN (vehicle registration)"
                               class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <textarea x-model="customerAddress" placeholder="Address" rows="2"
                                  class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                {{-- Payment method --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment method</label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="opt in paymentMethods" :key="opt.value">
                            <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition"
                                   :class="paymentMethod === opt.value ? 'border-2 border-green-600 bg-green-200 text-green-900' : 'border-2 border-slate-300 bg-slate-200 hover:bg-slate-300 text-slate-800'">
                                <input type="radio" x-model="paymentMethod" :value="opt.value" class="text-green-600">
                                <span class="text-sm font-medium" x-text="opt.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Card: present to terminal when configured --}}
                <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200" x-show="paymentMethod === 'card' && cardTerminalConfig.card_terminal_available">
                    <p class="text-sm text-blue-800 font-medium">Present card to terminal</p>
                    <p class="text-xs text-blue-600 mt-1">When you click Pay, the amount is sent to the terminal. Customer taps, inserts or swipes card.</p>
                </div>
                {{-- Cash: amount tendered --}}
                <div class="mb-4" x-show="paymentMethod === 'cash'">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount tendered (£)</label>
                    <input type="number" step="0.01" min="0" x-model="amountTendered"
                           placeholder="0.00"
                           class="w-full rounded-lg border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-slate-500 mt-1" x-show="paymentMethod === 'cash' && (parseFloat(amountTendered) || 0) >= total">
                        Change due: £<span x-text="(Math.max(0, (parseFloat(amountTendered) || 0) - total)).toFixed(2)"></span>
                    </p>
                    <p class="text-xs text-amber-600 mt-1" x-show="paymentMethod === 'cash' && (parseFloat(amountTendered) || 0) < total && amountTendered !== ''">
                        Amount is less than total. Enter sufficient amount.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <button type="button" id="epos-cancel-btn" @click="checkoutOpen = false"
                            class="flex-1 py-3 rounded-lg bg-slate-300 border border-slate-400 text-slate-900 hover:bg-slate-400 font-medium shadow">
                        Cancel
                    </button>
                    <button type="button" id="epos-pay-btn" @click="completeSale()"
                            class="flex-1 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium shadow-md disabled:bg-green-400 disabled:opacity-75"
                            :disabled="completing || !canPay"
                            :title="paymentMethod === 'cash' && !canPay ? 'Enter amount tendered' : ''">
                        <span x-show="!completing">Pay £<span x-text="total.toFixed(2)"></span></span>
                        <span x-show="completing">Processing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak]{display:none!important}
        /* Button colors - ensure no white buttons */
        #epos-product-grid button { background-color:#dbeafe !important; border-color:#93c5fd !important; color:#1e40af !important; }
        #epos-product-grid button:hover { background-color:#bfdbfe !important; border-color:#3b82f6 !important; }
        #epos-checkout-btn { background-color:#2563eb !important; color:#fff !important; }
        #epos-checkout-btn:hover:not(:disabled) { background-color:#1d4ed8 !important; }
        #epos-cancel-btn { background-color:#64748b !important; color:#fff !important; }
        #epos-cancel-btn:hover { background-color:#475569 !important; }
        #epos-pay-btn { background-color:#16a34a !important; color:#fff !important; }
        #epos-pay-btn:hover:not(:disabled) { background-color:#15803d !important; }
    </style>

    <script>
        window.eposApp = function eposApp(cardTerminalConfig = {}) {
            return {
                cardTerminalConfig: cardTerminalConfig,
                barcodeInput: '',
                serialInput: '',
                bookingIdInput: '',
                bookingError: '',
                currentBookingId: null,
                cart: [],
                tempIdCounter: 0,
                completing: false,
                completeError: '',
                checkoutOpen: false,
                paidSale: null,
                paymentMethod: 'cash',
                amountTendered: '',
                customerName: '',
                customerEmail: '',
                customerPhone: '',
                customerVrn: '',
                customerAddress: '',
                paymentMethods: [
                    { value: 'cash', label: 'Cash' },
                    { value: 'card', label: 'Card' },
                    { value: 'bank_transfer', label: 'Bank Transfer' },
                    { value: 'other', label: 'Other' },
                ],
                get hasSerialItems() {
                    return this.cart.some(i => i.requires_serial);
                },
                get total() {
                    return this.cart.reduce((sum, i) => sum + i.unit_price * (parseInt(i.quantity, 10) || 1), 0);
                },
                get canComplete() {
                    for (const item of this.cart) {
                        if (item.requires_serial && !(item.serial_number || '').trim()) return false;
                    }
                    return this.cart.length > 0;
                },
                get canPay() {
                    if (this.paymentMethod === 'cash') {
                        return (parseFloat(this.amountTendered) || 0) >= this.total;
                    }
                    return true;
                },
                openCheckout() {
                    if (!this.canComplete) {
                        if (this.cart.length === 0) {
                            this.completeError = 'Add items to cart first.';
                        } else {
                            this.completeError = 'Assign serial numbers for all items that require them.';
                        }
                        setTimeout(() => this.completeError = '', 4000);
                        return;
                    }
                    this.paymentMethod = 'cash';
                    this.amountTendered = '';
                    this.customerName = this.customerName || '';
                    this.customerEmail = this.customerEmail || '';
                    this.customerPhone = this.customerPhone || '';
                    this.customerVrn = this.customerVrn || '';
                    this.customerAddress = this.customerAddress || '';
                    this.paidSale = null;
                    this.checkoutOpen = true;
                },
                assignSerial() {
                    const sn = (this.serialInput || '').trim();
                    if (!sn) return;
                    const item = this.cart.find(i => i.requires_serial && !(i.serial_number || '').trim());
                    if (item) {
                        item.serial_number = sn;
                        this.serialInput = '';
                    }
                },
                loadBooking() {
                    const bid = this.bookingIdInput.trim();
                    if (!bid) return;
                    this.bookingError = '';
                    fetch('{{ route("admin.epos.lookup-by-booking") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ booking_id: bid })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.found && data.booking) {
                            const b = data.booking;
                            this.addProduct(b.product);
                            this.customerName = b.customer_name || '';
                            this.customerEmail = b.customer_email || '';
                            this.customerPhone = b.customer_phone || '';
                            this.customerVrn = b.vehicle_registration || '';
                            this.currentBookingId = b.booking_id;
                            this.bookingIdInput = '';
                        } else {
                            this.bookingError = data.message || 'Booking not found.';
                            setTimeout(() => this.bookingError = '', 4000);
                        }
                    })
                    .catch(() => { this.bookingError = 'Lookup failed.'; setTimeout(() => this.bookingError = '', 3000); });
                },
                lookupAndAdd() {
                    const bc = this.barcodeInput.trim();
                    if (!bc) return;
                    this.barcodeInput = '';
                    fetch('{{ route("admin.epos.lookup") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ barcode: bc })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.found) this.addProduct(data.product);
                        else this.completeError = 'Product not found: ' + bc;
                        setTimeout(() => this.completeError = '', 3000);
                    })
                    .catch(() => { this.completeError = 'Lookup failed.'; });
                },
                addProduct(p) {
                    const existing = this.cart.find(i => i.product_id === p.id && !i.requires_serial);
                    if (existing && !p.requires_serial) {
                        existing.quantity = Math.max(1, (parseInt(existing.quantity, 10) || 1) + 1);
                        return;
                    }
                    this.cart.push({
                        tempId: ++this.tempIdCounter,
                        product_id: p.id,
                        name: p.name,
                        unit_price: p.price,
                        quantity: 1,
                        serial_number: p.requires_serial ? (p.available_serials && p.available_serials[0] ? p.available_serials[0] : '') : '',
                        requires_serial: p.requires_serial || false,
                        available_serials: p.available_serials || [],
                    });
                },
                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                },
                async completeSale() {
                    if (!this.canComplete || this.completing || !this.canPay) return;
                    this.completing = true;
                    this.completeError = '';
                    let paymentReference = null;
                    if (this.paymentMethod === 'card' && this.cardTerminalConfig.card_terminal_available) {
                        const createRes = await fetch('{{ route("admin.epos.create-card-payment") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ amount: this.total })
                        }).then(r => r.json());
                        if (!createRes.success) {
                            this.completing = false;
                            this.completeError = createRes.message || 'Card payment failed.';
                            return;
                        }
                        paymentReference = createRes.payment_intent_id;
                        for (let i = 0; i < 120; i++) {
                            await new Promise(r => setTimeout(r, 1000));
                            const statusRes = await fetch('{{ route("admin.epos.card-payment-status") }}?payment_intent_id=' + encodeURIComponent(paymentReference), {
                                headers: { 'Accept': 'application/json' }
                            }).then(r => r.json());
                            if (statusRes.success && statusRes.succeeded) break;
                            if (statusRes.success && statusRes.status === 'canceled') {
                                this.completing = false;
                                this.completeError = 'Payment cancelled.';
                                return;
                            }
                        }
                        const finalCheck = await fetch('{{ route("admin.epos.card-payment-status") }}?payment_intent_id=' + encodeURIComponent(paymentReference), { headers: { 'Accept': 'application/json' } }).then(r => r.json());
                        if (!finalCheck.success || !finalCheck.succeeded) {
                            this.completing = false;
                            this.completeError = 'Card payment not confirmed. Please try again.';
                            return;
                        }
                    }
                    const items = this.cart.map(i => ({
                        product_id: i.product_id,
                        quantity: Math.max(1, parseInt(i.quantity, 10) || 1),
                        unit_price: i.unit_price,
                        serial_number: i.requires_serial ? (i.serial_number || null) : null,
                    }));
                    const payload = {
                        items,
                        payment_method: this.paymentMethod,
                        amount_tendered: this.paymentMethod === 'cash' ? (parseFloat(this.amountTendered) || null) : null,
                        payment_reference: paymentReference,
                        customer_name: (this.customerName || '').trim() || null,
                        customer_email: (this.customerEmail || '').trim() || null,
                        customer_phone: (this.customerPhone || '').trim() || null,
                        customer_vrn: (this.customerVrn || '').trim().toUpperCase() || null,
                        customer_address: (this.customerAddress || '').trim() || null,
                        booking_id: this.currentBookingId || null,
                    };
                    fetch('{{ route("admin.epos.complete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.completing = false;
                        if (data.success) {
                            const pm = this.paymentMethod;
                            this.cart = [];
                            this.amountTendered = '';
                            this.customerName = '';
                            this.customerEmail = '';
                            this.customerPhone = '';
                            this.customerVrn = '';
                            this.customerAddress = '';
                            this.currentBookingId = null;
                            this.paidSale = { reference: data.sale.reference, total: data.sale.total, payment_method: pm, saleId: data.sale.id };
                            this.checkoutOpen = true;
                        } else {
                            this.completeError = data.message || 'Sale failed.';
                        }
                    })
                    .catch(() => {
                        this.completing = false;
                        this.completeError = 'Request failed.';
                    });
                }
            };
        }
    </script>
</x-admin-layout>
