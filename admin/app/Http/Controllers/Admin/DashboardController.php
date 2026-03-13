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
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
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

        // Monthly revenue for bar chart (last 12 months) - db-agnostic
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $key = $d->format('Y-m');
            $total = Sale::whereNotNull('completed_at')
                ->whereYear('completed_at', $d->year)
                ->whereMonth('completed_at', $d->month)
                ->sum('total');
            $months[$key] = (float) $total;
        }
        $monthlyRevenue = $months;

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

        // Latest customers (recent sales with customer names, unique, with purchase count)
        $latestCustomers = Sale::whereNotNull('completed_at')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->orderByDesc('completed_at')
            ->get()
            ->unique('customer_name')
            ->take(5)
            ->values()
            ->map(function ($s) {
                $count = Sale::where('customer_name', $s->customer_name)->count();
                return (object)['customer_name' => $s->customer_name, 'purchases' => $count];
            });

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
