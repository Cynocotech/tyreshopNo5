<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $sales = Sale::with(['items.product', 'user:id,name'])
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->orderByDesc('completed_at')
            ->get();

        $summary = $this->buildSummary($from, $to);

        return view('admin.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function journal(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $sales = Sale::with(['items.product', 'user:id,name'])
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->orderBy('completed_at')
            ->orderBy('id')
            ->get();

        $summary = $this->buildSummary($from, $to);

        return view('admin.sales.journal', [
            'sales' => $sales,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function dailySummary(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $daily = Sale::query()
            ->selectRaw('DATE(completed_at) as sale_date, COUNT(*) as count, SUM(total) as total')
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->whereNotNull('completed_at')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $byPayment = Sale::query()
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->whereNotNull('completed_at')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $grandTotal = $daily->sum('total');
        $totalTransactions = $daily->sum('count');

        return view('admin.sales.daily-summary', [
            'daily' => $daily,
            'byPayment' => $byPayment,
            'grandTotal' => $grandTotal,
            'totalTransactions' => $totalTransactions,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $sales = Sale::with(['items.product'])
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->orderBy('completed_at')
            ->get();

        $filename = 'sales-' . $from . '_to_' . $to . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Reference', 'Customer', 'VRN', 'Item', 'Qty', 'Unit Price', 'Line Total', 'Payment Method', 'Sale Total']);
            foreach ($sales as $sale) {
                $date = $sale->completed_at?->format('Y-m-d H:i') ?? '';
                $customer = trim(implode(' ', array_filter([$sale->customer_name, $sale->customer_phone])));
                $first = true;
                foreach ($sale->items as $item) {
                    $productName = $item->product?->name ?? 'Product';
                    fputcsv($out, [
                        $first ? $date : '',
                        $first ? $sale->reference : '',
                        $first ? $customer : '',
                        $first ? ($sale->customer_vrn ?? '') : '',
                        $productName,
                        $item->quantity,
                        number_format((float) $item->unit_price, 2),
                        number_format((float) $item->total, 2),
                        $first ? ucfirst(str_replace('_', ' ', $sale->payment_method ?? '')) : '',
                        $first ? number_format((float) $sale->total, 2) : '',
                    ]);
                    $first = false;
                }
                if ($sale->items->isEmpty()) {
                    fputcsv($out, [$date, $sale->reference, $customer, $sale->customer_vrn ?? '', '', '', '', '', ucfirst(str_replace('_', ' ', $sale->payment_method ?? '')), number_format((float) $sale->total, 2)]);
                }
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroyBulk(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:sales,id'])['ids'];
        $count = Sale::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', "Deleted {$count} sale(s).");
    }

    public function destroyBulkByDates(Request $request): RedirectResponse
    {
        $dates = $request->validate(['dates' => 'required|array', 'dates.*' => 'date'])['dates'];
        $count = Sale::whereNotNull('completed_at')
            ->whereIn(DB::raw('DATE(completed_at)'), $dates)
            ->delete();
        $redirect = redirect()->route('admin.sales.daily', [
            'from' => $request->input('from', now()->startOfMonth()->format('Y-m-d')),
            'to' => $request->input('to', now()->format('Y-m-d')),
        ]);
        return $redirect->with('success', "Deleted {$count} sale(s) from selected dates.");
    }

    protected function buildSummary(string $from, string $to): array
    {
        $totals = Sale::query()
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->whereDate('completed_at', '>=', $from)
            ->whereDate('completed_at', '<=', $to)
            ->whereNotNull('completed_at')
            ->groupBy('payment_method')
            ->get();

        return [
            'grand_total' => $totals->sum('total'),
            'transaction_count' => $totals->sum('count'),
            'by_method' => $totals->keyBy('payment_method'),
        ];
    }
}
