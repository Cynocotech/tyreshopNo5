<x-admin-layout>
    <x-slot name="header">Products</x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Product</a>
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
</x-admin-layout>
