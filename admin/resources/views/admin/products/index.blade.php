<x-admin-layout>
    <x-slot name="header">Products</x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Product</a>
    </div>

    {{-- Receive Stock: 2D / barcode / QR scanner --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 border-emerald-600" x-data="receiveStock()">
        <h3 class="text-base font-semibold text-slate-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Receive stock (scan barcode / QR)
        </h3>
        <p class="text-sm text-slate-600 mb-3">Use a 2D barcode scanner or QR reader to add tyres/products to inventory. Each scan adds 1 to stock (or enter quantity below). Works with Bluetooth/USB scanners.</p>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="scan-input" class="block text-sm font-medium text-slate-700 mb-1">Scan here</label>
                <div class="flex gap-2">
                    <input type="text" id="scan-input" x-ref="scanInput" x-model="barcode" @keydown.enter.prevent="addStock()"
                           placeholder="Focus and scan, or use camera..."
                           class="flex-1 rounded-lg border-slate-300 py-3 px-4 text-base focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           autofocus>
                    <button type="button" @click="openCameraScanner()"
                            class="flex items-center gap-2 px-4 py-3 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"/></svg>
                        <span class="hidden sm:inline">Camera</span>
                    </button>
                </div>
            </div>
            <div class="w-24">
                <label for="scan-qty" class="block text-sm font-medium text-slate-700 mb-1">Qty</label>
                <input type="number" id="scan-qty" x-model.number="quantity" min="1" max="9999"
                       class="w-full rounded-lg border-slate-300 py-2 px-3">
            </div>
            <button type="button" @click="addStock()" class="px-5 py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                Add to stock
            </button>
        </div>
        <p class="text-sm mt-2" :class="messageType === 'success' ? 'text-emerald-600' : 'text-red-600'" x-show="message" x-text="message" x-cloak></p>

        {{-- Full-screen camera barcode/QR scanner modal --}}
        <div id="camera-scanner-modal" class="fixed inset-0 z-[100]" x-show="cameraOpen" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/95 flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-800/80 text-white shrink-0">
                <h3 class="text-lg font-semibold">Scan barcode or QR code</h3>
                <button type="button" @click="closeCameraScanner()" class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors" aria-label="Close">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="camera-scanner-box" class="flex-1 flex items-center justify-center min-h-0 p-4">
                {{-- html5-qrcode renders the camera viewfinder here --}}
            </div>
            <p class="text-center text-slate-400 text-sm py-3">Point your camera at a barcode or QR code</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Barcode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase">Qty / Inventory</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($products as $p)
                <tr>
                    <td class="px-4 py-3 font-medium flex items-center gap-2">
                        <x-product-icon :icon="$p->icon ?: 'package'" class="w-5 h-5 text-slate-500 shrink-0" />
                        {{ $p->name }}
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $p->barcode ?? '–' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $p->sku ?? '–' }}</td>
                    <td class="px-4 py-3">£{{ number_format($p->price, 2) }}</td>
                    <td class="px-4 py-3">
                        @if($p->requires_serial)
                            {{ $p->available_serials_count ?? $p->serials()->where('sold', false)->count() }} in stock
                            @if($p->isLowStock())<span class="text-amber-600 font-medium ml-1">(low)</span>@endif
                        @else
                            {{ $p->quantity ?? 0 }}
                            @if($p->isLowStock())<span class="text-amber-600 font-medium ml-1">(low)</span>@endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.products.edit', $p) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No products yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    document.addEventListener('alpine:init', function() {
        Alpine.data('receiveStock', function() {
            return {
                barcode: '',
                quantity: 1,
                message: '',
                messageType: 'success',
                cameraOpen: false,
                _cameraScanner: null,
                openCameraScanner() {
                    if (typeof Html5Qrcode === 'undefined') {
                        this.message = 'Scanner library loading...';
                        this.messageType = 'error';
                        setTimeout(() => this.openCameraScanner(), 500);
                        return;
                    }
                    this.cameraOpen = true;
                    this.message = '';
                    const comp = this;
                    setTimeout(() => {
                        const el = document.getElementById('camera-scanner-box');
                        if (!el) return;
                        el.innerHTML = '';
                        const scanner = new Html5Qrcode('camera-scanner-box');
                        comp._cameraScanner = scanner;
                        scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 250 },
                            (decodedText) => {
                                comp.barcode = decodedText;
                                comp.addStock();
                                scanner.stop();
                                comp._cameraScanner = null;
                                comp.cameraOpen = false;
                            },
                            () => {}
                        ).catch(err => {
                            comp.message = 'Camera access failed: ' + (err.message || 'Permission denied');
                            comp.messageType = 'error';
                            comp.cameraOpen = false;
                        });
                    });
                },
                closeCameraScanner() {
                    if (this._cameraScanner) {
                        this._cameraScanner.stop().catch(() => {});
                        this._cameraScanner = null;
                    }
                    this.cameraOpen = false;
                },
                addStock() {
                    const barcode = this.barcode.trim();
                    if (!barcode) return;
                    this.message = '';
                    fetch('{{ route("admin.products.add-stock-by-scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ barcode: barcode, quantity: this.quantity })
                    })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            this.message = data.message;
                            this.messageType = 'success';
                            this.barcode = '';
                            window.location.reload();
                        } else {
                            this.message = (data && data.message) || 'Failed to add stock.';
                            this.messageType = 'error';
                            setTimeout(() => this.message = '', 6000);
                        }
                    })
                    .catch(() => {
                        this.message = 'Request failed.';
                        this.messageType = 'error';
                    });
                }
            };
        });
    });
    </script>
</x-admin-layout>
