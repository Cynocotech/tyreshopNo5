<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'week');
        $now = Carbon::now();

        if ($period === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $label = 'This week';
        } elseif ($period === 'last_week') {
            $start = $now->copy()->subWeek()->startOfWeek();
            $end = $now->copy()->subWeek()->endOfWeek();
            $label = 'Last week';
        } elseif ($period === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $label = 'This month';
        } else {
            $period = 'last_month';
            $start = $now->copy()->subMonth()->startOfMonth();
            $end = $now->copy()->subMonth()->endOfMonth();
            $label = 'Last month';
        }

        $soldInPeriod = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as sold'))
            ->groupBy('sale_items.product_id')
            ->pluck('sold', 'product_id');

        $products = Product::with('category')
            ->withCount(['serials', 'availableSerials'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($soldInPeriod) {
                $stock = $p->requires_serial
                    ? (int) ($p->available_serials_count ?? $p->availableSerials()->count())
                    : (int) ($p->quantity ?? 0);
                return (object) [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category?->name,
                    'tyre_size' => $p->tyre_size,
                    'stock' => $stock,
                    'sold' => (int) ($soldInPeriod[$p->id] ?? 0),
                    'requires_serial' => $p->requires_serial,
                    'low_stock' => $p->isLowStock(),
                ];
            });

        return view('admin.inventory.index', [
            'products' => $products,
            'period' => $period,
            'periodLabel' => $label,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
