<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SiteSetting;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EposController extends Controller
{
    protected function businessData(): array
    {
        return [
            'name' => SiteSetting::get('site_name', 'NO5 Tyre & MOT Service'),
            'address' => trim(implode(', ', array_filter([
                SiteSetting::get('address_street'),
                SiteSetting::get('address_locality'),
                SiteSetting::get('address_region'),
                SiteSetting::get('address_postcode'),
                SiteSetting::get('address_country'),
            ]))),
            'phone' => SiteSetting::get('phone', '07895 859505'),
            'email' => SiteSetting::get('email', 'info@no5mot.co.uk'),
        ];
    }

    public function index(PaymentGatewayService $paymentGateway): View
    {
        return view('admin.epos.index', [
            'products' => Product::with(['availableSerials', 'category'])
                ->orderBy('sort_order')->orderBy('name')->get(),
            'cardTerminalConfig' => $paymentGateway->cardTerminalConfig(),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $barcode = trim($request->input('barcode', ''));
        if (!$barcode) {
            return response()->json(['found' => false]);
        }
        $product = Product::where('barcode', $barcode)->orWhere('sku', $barcode)->first();
        if ($product) {
            return response()->json([
                'found' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => (float) $product->price,
                    'requires_serial' => $product->requires_serial,
                    'icon' => $product->icon ?: 'package',
                    'available_serials' => $product->requires_serial ? $product->availableSerials->pluck('serial_number') : [],
                ],
            ]);
        }
        return response()->json(['found' => false]);
    }

    public function lookupByBooking(Request $request): JsonResponse
    {
        $bookingId = trim($request->input('booking_id', ''));
        if (!$bookingId) {
            return response()->json(['found' => false, 'message' => 'Enter a booking ID']);
        }
        $booking = Booking::where('booking_id', $bookingId)->active()->first();
        if (!$booking) {
            $canceled = Booking::where('booking_id', $bookingId)->canceled()->first();
            if ($canceled) {
                return response()->json(['found' => false, 'message' => 'This booking was canceled.']);
            }
            return response()->json(['found' => false, 'message' => 'Booking not found.']);
        }
        $bookingSvc = Product::where('sku', 'BOOKING-SVC')->first();
        if (!$bookingSvc) {
            return response()->json(['found' => false, 'message' => 'Booking Service product not configured. Run migrations.']);
        }
        $amount = (float) ($booking->total_amount ?? 0);
        return response()->json([
            'found' => true,
            'booking' => [
                'id' => $booking->id,
                'booking_id' => $booking->booking_id,
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'vehicle_registration' => $booking->vehicle_registration,
                'service_type' => $booking->service_type,
                'total_amount' => $amount,
                'product' => [
                    'id' => $bookingSvc->id,
                    'name' => ($booking->service_type ?? 'Booking') . ' — ' . $booking->booking_id,
                    'price' => $amount,
                    'requires_serial' => false,
                    'available_serials' => [],
                    'is_booking' => true,
                ],
            ],
        ]);
    }

    public function addSerial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'serial_number' => 'required|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        if (!$product->requires_serial) {
            return response()->json(['success' => false, 'message' => 'Product does not require serial numbers.'], 422);
        }

        $serial = ProductSerial::firstOrCreate(
            ['product_id' => $product->id, 'serial_number' => trim($validated['serial_number'])],
            ['sold' => false]
        );

        if ($serial->wasRecentlyCreated) {
            return response()->json(['success' => true, 'serial' => ['id' => $serial->id, 'serial_number' => $serial->serial_number]]);
        }

        return response()->json(['success' => false, 'message' => 'Serial number already exists for this product.'], 422);
    }

    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.serial_number' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,other',
            'amount_tendered' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:50',
            'customer_vrn' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'booking_id' => 'nullable|string|max:64',
        ]);

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'reference' => Sale::generateRef(),
                'total' => 0,
                'user_id' => auth()->id(),
                'completed_at' => now(),
                'payment_method' => $validated['payment_method'],
                'booking_id' => $validated['booking_id'] ?? null,
                'amount_tendered' => $validated['amount_tendered'] ?? null,
                'payment_reference' => $validated['payment_reference'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_vrn' => isset($validated['customer_vrn']) ? strtoupper(trim($validated['customer_vrn'])) : null,
                'customer_address' => $validated['customer_address'] ?? null,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->requires_serial) {
                    if (empty($item['serial_number'])) {
                        throw new \RuntimeException("Serial number required for {$product->name}");
                    }
                } else {
                    $isBookingSvc = $product->sku === 'BOOKING-SVC';
                    if (!$isBookingSvc) {
                        $stock = (int) $product->quantity;
                        if ($item['quantity'] > $stock) {
                            throw new \RuntimeException("Insufficient stock for {$product->name}. Available: {$stock}");
                        }
                    }
                }

                $itemTotal = $item['unit_price'] * $item['quantity'];
                $total += $itemTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                ]);

                if ($product->requires_serial && !empty($item['serial_number'])) {
                    ProductSerial::where('product_id', $item['product_id'])
                        ->where('serial_number', $item['serial_number'])
                        ->update(['sold' => true, 'sold_at' => now()]);
                } elseif (!$product->requires_serial && $product->sku !== 'BOOKING-SVC') {
                    $product->decrement('quantity', $item['quantity']);
                }
            }

            $sale->update(['total' => $total]);
            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => ['id' => $sale->id, 'reference' => $sale->reference, 'total' => (float) $sale->total],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['items.product']);
        return view('admin.sales.receipt', [
            'sale' => $sale,
            'business' => $this->businessData(),
        ]);
    }

    public function createCardPayment(Request $request, PaymentGatewayService $paymentGateway): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);
        $amountPence = (float) $validated['amount'] * 100;

        $config = $paymentGateway->cardTerminalConfig();
        if (!$config['card_terminal_available']) {
            return response()->json(['success' => false, 'message' => 'Card terminal not configured. Configure in Settings → Payment Gateways.']);
        }

        if ($config['provider'] === 'stripe_terminal') {
            $result = $paymentGateway->createStripeTerminalPayment($amountPence);
        } elseif ($config['provider'] === 'teya_spi') {
            return response()->json(['success' => false, 'message' => 'Teya SPI integration coming soon.']);
        } else {
            return response()->json(['success' => false, 'message' => 'No card terminal provider selected.']);
        }

        return response()->json($result);
    }

    public function cardPaymentStatus(Request $request, PaymentGatewayService $paymentGateway): JsonResponse
    {
        $paymentIntentId = $request->input('payment_intent_id');
        if (!$paymentIntentId) {
            return response()->json(['success' => false, 'message' => 'Missing payment_intent_id']);
        }

        $result = $paymentGateway->getStripePaymentStatus($paymentIntentId);
        return response()->json($result);
    }
}
