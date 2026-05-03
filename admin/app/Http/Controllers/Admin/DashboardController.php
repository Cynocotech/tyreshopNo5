<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::withCount('availableSerials')->get();
        $lowStockProducts = $products->filter(fn ($p) => $p->isLowStock())->values();

        $topServices = Booking::query()
            ->selectRaw('service_type as name, COUNT(*) as count')
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->groupBy('service_type')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $topProducts = SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product:id,name')
            ->get();

        $salesThisMonth = Sale::whereNotNull('completed_at')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year);
        $salesRevenueMonth = (clone $salesThisMonth)->sum('total');
        $salesCountMonth = (clone $salesThisMonth)->count();

        // Last 90 days totals for progress indicators
        $salesLast90 = Sale::whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(90));
        $ordersLast90 = (clone $salesLast90)->count();
        $revenueLast90 = (clone $salesLast90)->sum('total');

        // Monthly revenue (last 12 months) — one grouped query instead of 12 round-trips
        $from = now()->subMonths(11)->startOfMonth();
        $driver = Sale::query()->getConnection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', completed_at)"
            : "DATE_FORMAT(completed_at, '%Y-%m')";
        $rows = Sale::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $from)
            ->selectRaw("{$monthExpr} as ym, SUM(total) as revenue")
            ->groupBy(DB::raw($monthExpr))
            ->pluck('revenue', 'ym');
        $monthlyRevenue = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyRevenue[$key] = (float) ($rows[$key] ?? 0);
        }

        // Recent sales (last 10)
        $recentSales = Sale::with(['items.product'])
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // Today's best sale (single highest)
        $todayBest = Sale::whereNotNull('completed_at')
            ->whereDate('completed_at', today())
            ->orderByDesc('total')
            ->first();

        // Latest customers — one grouped query (avoid N+1 count per name)
        $latestCustomers = Sale::query()
            ->whereNotNull('completed_at')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->selectRaw('customer_name, COUNT(*) as purchases, MAX(completed_at) as last_sale_at')
            ->groupBy('customer_name')
            ->orderByDesc('last_sale_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => (object) ['customer_name' => $r->customer_name, 'purchases' => (int) $r->purchases]);

        return view('admin.dashboard', [
            'servicesCount' => Service::count(),
            'categoriesCount' => ServiceCategory::count(),
            'faqsCount' => Faq::count(),
            'areasCount' => Area::count(),
            'salesRevenueMonth' => $salesRevenueMonth,
            'salesCountMonth' => $salesCountMonth,
            'ordersLast90' => $ordersLast90,
            'revenueLast90' => $revenueLast90,
            'monthlyRevenue' => $monthlyRevenue,
            'lowStockProducts' => $lowStockProducts,
            'topServices' => $topServices,
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'todayBest' => $todayBest,
            'latestCustomers' => $latestCustomers,
        ]);
    }
}
