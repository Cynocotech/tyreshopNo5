<x-admin-layout>
    <x-slot name="header">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</x-slot>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" class="bg-white rounded-lg shadow p-4 sm:p-6 max-w-2xl w-full mx-auto" x-data="productForm({{ json_encode(['barcode' => old('barcode', $product->barcode ?? '')]) }})"
          @submit="document.querySelector('input[name=barcode]').value = barcodeValue">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded border-slate-300" required>
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select name="product_category_id" class="w-full rounded border-slate-300">
                        <option value="">— None —</option>
                        @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ old('product_category_id', $product->product_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tyre size</label>
                    <input type="text" name="tyre_size" value="{{ old('tyre_size', $product->tyre_size) }}" class="w-full rounded border-slate-300" placeholder="205/55 R16" maxlength="50">
                    <p class="text-xs text-slate-500 mt-0.5">e.g. 205/55 R16 — for tyres only</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="barcode" x-model="barcodeValue" @keydown.enter.prevent
                               class="flex-1 min-w-0 rounded-lg border-slate-300 py-2.5 px-3 sm:py-2" placeholder="Scan or type"
                               title="Focus here and scan with barcode scanner">
                        <div class="flex gap-2 shrink-0">
                            <button type="button" @click="openCameraScanner()"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 sm:py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"/></svg>
                                <span>Camera</span>
                            </button>
                            <button type="button" @click="generateBarcode()" class="px-3 py-2.5 sm:py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm">
                                Generate
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Scan with scanner, use camera, or type. Click Generate to create one.</p>
                    @error('barcode')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    <div x-show="barcodeValue" x-cloak class="mt-2 p-2 bg-white border border-slate-200 rounded inline-block max-w-full overflow-hidden" x-effect="barcodeValue && typeof JsBarcode !== 'undefined' && JsBarcode('#barcode-preview', barcodeValue, { format: 'CODE128', width: 2, height: 50 })">
                        <svg id="barcode-preview" class="max-w-full min-w-0" height="50"></svg>
                    </div>
                    <p class="text-xs text-red-600 mt-1" x-show="cameraError" x-text="cameraError" x-cloak></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded border-slate-300" placeholder="Optional">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Price (£)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $product->price ?? 0) }}" class="w-full rounded border-slate-300" required>
                    @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="w-24 rounded border-slate-300">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Icon (shown on EPOS)</label>
                <div class="flex flex-wrap gap-3 items-center">
                    @foreach(\App\Models\Product::iconOptions() as $value => $label)
                    <label class="flex items-center gap-2 p-2 rounded-lg border-2 cursor-pointer transition {{ old('icon', $product->icon ?? '') === $value ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="icon" value="{{ $value }}" {{ old('icon', $product->icon ?? '') === $value ? 'checked' : '' }} class="sr-only">
                        <x-product-icon :icon="$value" class="w-6 h-6 text-slate-600" />
                        <span class="text-xs text-slate-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="requires_serial" value="1" {{ old('requires_serial', $product->requires_serial ?? false) ? 'checked' : '' }} x-model="requiresSerial">
                    <span class="text-sm text-slate-700">Requires serial number</span>
                </label>
            </div>

            {{-- Quantity (for non-serial products) --}}
            <div x-show="!requiresSerial" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quantity (stock)</label>
                    <input type="number" name="quantity" min="0" value="{{ old('quantity', $product->quantity ?? 0) }}" class="w-32 rounded border-slate-300" :disabled="requiresSerial">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Low stock alert at</label>
                    <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" class="w-24 rounded border-slate-300" placeholder="5">
                    <p class="text-xs text-slate-500 mt-0.5">Alert when stock falls to or below this</p>
                </div>
            </div>

            {{-- For serial products: low_stock_threshold (quantity stays 0 — quantity input above is disabled when requires serial) --}}
            <div x-show="requiresSerial" x-cloak class="border border-slate-200 rounded-lg p-4 bg-slate-50 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Low stock alert at</label>
                    <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" class="w-24 rounded border-slate-300" placeholder="5">
                    <p class="text-xs text-slate-500 mt-0.5">Alert when available serials fall to or below this</p>
                </div>
            </div>

            {{-- Add Serial section (visible when requires_serial) --}}
            <div id="add-serial-section" class="border border-slate-200 rounded-lg p-4 bg-slate-50" x-show="requiresSerial" x-cloak x-transition>
                <h4 class="text-sm font-medium text-slate-700 mb-2">Serial numbers (inventory)</h4>
                @if($product->exists)
                    <div class="mb-3">
                        <div class="flex flex-col sm:flex-row gap-2 mb-2">
                            <input type="text" x-ref="serialInput" placeholder="Scan or enter serial number" class="flex-1 min-w-0 rounded border-slate-300"
                                   @keydown.enter.prevent="addSerial()" x-model="newSerial" title="Focus and scan with 2D/QR scanner">
                            <button type="button" @click="addSerial()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Add Serial</button>
                        </div>
                        <p class="text-xs text-amber-600" x-show="serialError" x-text="serialError"></p>
                        <p class="text-xs text-green-600" x-show="serialSuccess" x-text="serialSuccess"></p>
                    </div>
                    <ul class="space-y-1 text-sm" id="serials-list">
                        @foreach($product->serials as $s)
                        <li class="flex items-center justify-between py-1 px-2 bg-white rounded border border-slate-200">
                            <span>{{ $s->serial_number }}</span>
                            <span class="text-slate-400 text-xs">{{ $s->sold ? 'Sold' : 'Available' }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <p class="text-sm text-slate-500 py-2" id="serials-empty" @if(!$product->serials->isEmpty()) style="display:none" @endif>No serials yet. Add one above.</p>
                @else
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Initial serials (one per line)</label>
                        <textarea name="serials" rows="4" class="w-full rounded border-slate-300" placeholder="SN001&#10;SN002">{{ old('serials') }}</textarea>
                        <p class="text-xs text-slate-500">Add serial numbers after saving, or paste multiple here.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Camera barcode scanner modal --}}
        <div id="product-barcode-scanner-modal" class="fixed inset-0 z-[100]" x-show="cameraOpen" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-slate-900/95 flex flex-col">
                <div class="flex items-center justify-between px-4 py-3 sm:px-6 bg-slate-800/80 text-white shrink-0">
                    <h3 class="text-base sm:text-lg font-semibold truncate pr-2">Scan barcode or QR code</h3>
                    <button type="button" @click="closeCameraScanner()" class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors shrink-0" aria-label="Close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="product-barcode-scanner-box" class="flex-1 flex items-center justify-center min-h-0 p-2 sm:p-4 overflow-hidden">
                    {{-- html5-qrcode camera viewfinder --}}
                </div>
                <p class="text-center text-slate-400 text-xs sm:text-sm py-2 sm:py-3 px-4">Point your camera at a barcode or QR code</p>
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2">
            <button type="submit" class="px-4 py-2.5 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancel</a>
        </div>
    </form>

    <script>
        function productForm(initialData = {}) {
            const productId = {{ $product->id ?? 'null' }};
            return {
                barcodeValue: initialData.barcode || '',
                requiresSerial: {{ old('requires_serial', $product->requires_serial ?? false) ? 'true' : 'false' }},
                newSerial: '',
                serialError: '',
                serialSuccess: '',
                productId: {{ $product->id ?? 'null' }},
                cameraOpen: false,
                cameraError: '',
                _cameraScanner: null,
                openCameraScanner() {
                    if (typeof Html5Qrcode === 'undefined') {
                        this.cameraError = 'Scanner loading…';
                        setTimeout(() => { this.cameraError = ''; this.openCameraScanner(); }, 500);
                        return;
                    }
                    this.cameraOpen = true;
                    this.cameraError = '';
                    const comp = this;
                    setTimeout(() => {
                        const el = document.getElementById('product-barcode-scanner-box');
                        if (!el) return;
                        el.innerHTML = '';
                        const scanner = new Html5Qrcode('product-barcode-scanner-box');
                        comp._cameraScanner = scanner;
                        const qrbox = Math.max(150, Math.min(300, window.innerWidth - 32, window.innerHeight - 120));
                        scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: qrbox },
                            (decodedText) => {
                                comp.barcodeValue = decodedText;
                                scanner.stop().catch(() => {});
                                comp._cameraScanner = null;
                                comp.cameraOpen = false;
                            },
                            () => {}
                        ).catch(err => {
                            comp.cameraError = 'Camera failed: ' + (err.message || 'Permission denied');
                            comp.cameraOpen = false;
                            comp._cameraScanner = null;
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
                generateBarcode() {
                    const id = this.productId || ('t' + Date.now().toString(36));
                    this.barcodeValue = 'PRD' + String(id).padStart(6, '0');
                },
                addSerial() {
                    if (!this.newSerial.trim() || !this.productId) return;
                    this.serialError = '';
                    this.serialSuccess = '';
                    const serial = this.newSerial.trim();
                    fetch('{{ route("admin.epos.add-serial") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ product_id: this.productId, serial_number: serial })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.serialSuccess = 'Serial added.';
                            this.newSerial = '';
                            const ul = document.getElementById('serials-list');
                            const empty = document.getElementById('add-serial-section')?.querySelector('p.text-slate-500');
                            if (empty) empty.remove();
                            const li = document.createElement('li');
                            li.className = 'flex items-center justify-between py-1 px-2 bg-white rounded border border-slate-200';
                            li.innerHTML = '<span>' + serial + '</span><span class="text-slate-400 text-xs">Available</span>';
                            ul.appendChild(li);
                            setTimeout(() => this.serialSuccess = '', 2000);
                        } else {
                            this.serialError = data.message || 'Failed to add serial.';
                        }
                    })
                    .catch(() => { this.serialError = 'Request failed.'; });
                }
            };
        }
    </script>
</x-admin-layout>
