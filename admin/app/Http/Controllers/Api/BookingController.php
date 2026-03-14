<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;
use Stripe\Webhook;

class BookingController extends Controller
{
    private const ALL_SLOTS = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'];

    public function config(): JsonResponse
    {
        return response()->json([
            'stripePublishableKey' => config('services.stripe.publishable'),
            'hasTelegram' => !empty(config('services.telegram.bot_token')) && !empty(config('services.telegram.chat_id')),
        ]);
    }

    public function availableSlots(Request $request): JsonResponse
    {
        $date = trim((string) $request->query('date', ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['available' => []]);
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if ($d && (int) $d->format('w') === 0) {
            return response()->json(['available' => []]);
        }
        // Read from DB (admin panel + stripe bookings) and merge with JSON (legacy)
        $bookedFromDb = Booking::active()
            ->whereDate('appointment_date', $date)
            ->pluck('appointment_time')
            ->map(fn ($t) => preg_replace('/\s+/', '', (string) $t))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $bookedFromJson = $this->readSlots()[$date] ?? [];
        $booked = array_values(array_unique(array_merge($bookedFromDb, $bookedFromJson)));
        return response()->json(['available' => array_values(array_diff(self::ALL_SLOTS, $booked))]);
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        $valid = $request->validate([
            'customerEmail' => 'required|email',
            'vehicleRegistration' => 'required|string',
            'appointmentDate' => 'required|string',
            'appointmentTime' => 'required|string',
            'serviceType' => 'nullable|string',
            'totalAmount' => 'nullable|numeric',
        ]);
        $amount = isset($valid['totalAmount']) ? (int) round((float) $valid['totalAmount'] * 100) : 1900;
        $bookingId = 'N05-' . time();
        $stripe = $this->stripe();
        if (!$stripe) {
            return response()->json(['error' => 'Stripe not configured'], 503);
        }
        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => $valid['serviceType'] ?? 'MOT Test',
                            'description' => ($valid['vehicleMake'] ?? '') . ' ' . ($valid['vehicleModel'] ?? '') . ' (' . $valid['vehicleRegistration'] . ') — ' . $valid['appointmentDate'] . ' at ' . $valid['appointmentTime'],
                            'images' => [],
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/mot-booking') . '?success=1&booking=' . $bookingId,
                'cancel_url' => url('/mot-booking') . '?cancel=1',
                'customer_email' => $valid['customerEmail'],
                'metadata' => [
                    'bookingId' => $bookingId,
                    'customerName' => $request->input('customerName', ''),
                    'customerPhone' => $request->input('customerPhone', ''),
                    'vehicleRegistration' => $valid['vehicleRegistration'],
                    'vehicleMake' => $request->input('vehicleMake', ''),
                    'vehicleModel' => $request->input('vehicleModel', ''),
                    'appointmentDate' => $valid['appointmentDate'],
                    'appointmentTime' => $valid['appointmentTime'],
                    'serviceType' => $valid['serviceType'] ?? 'MOT Test',
                    'totalAmount' => (string) ($amount / 100),
                ],
            ]);
            return response()->json([
                'sessionId' => $session->id,
                'publishableKey' => config('services.stripe.key'),
                'bookingId' => $bookingId,
                'url' => $session->url,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Could not create checkout session', 'detail' => $e->getMessage()], 500);
        }
    }

    public function confirmBooking(Request $request): JsonResponse
    {
        $m = $request->input('metadata', $request->all());
        $email = $request->input('customer_email') ?? $m['customerEmail'] ?? $m['customer_email'] ?? null;
        if (!$email || empty($m['vehicleRegistration'])) {
            return response()->json(['error' => 'Missing email or vehicleRegistration'], 400);
        }
        $date = $m['appointmentDate'] ?? null;
        $time = $m['appointmentTime'] ?? null;
        if ($date && $time) {
            $timeNorm = preg_replace('/\s+/', '', $time);
            $bookedFromDb = Booking::active()
                ->whereDate('appointment_date', $date)
                ->pluck('appointment_time')
                ->map(fn ($t) => preg_replace('/\s+/', '', (string) $t))
                ->toArray();
            $bookedFromJson = $this->readSlots()[$date] ?? [];
            $booked = array_merge($bookedFromDb, $bookedFromJson);
            if (in_array($timeNorm, $booked, true)) {
                return response()->json(['error' => 'This time slot has already been booked. Please choose another date or time.'], 409);
            }
        }
        try {
            $this->notifyAndEmail($m, $email);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stripeWebhook(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        $secret = config('services.stripe.webhook_secret');
        $sig = $request->header('Stripe-Signature');
        if (!$secret || !$sig) {
            return response('Webhook secret not configured', 400);
        }
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $sig,
                $secret
            );
        } catch (\Throwable $e) {
            return response('Webhook Error: ' . $e->getMessage(), 400);
        }
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->notifyAndEmail((array) $session->metadata, $session->customer_email ?? '');
        }
        return response()->json(['received' => true]);
    }

    public function motNotify(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $vrm = $request->input('vrm');
        if (!$email || !$vrm) {
            return response()->json(['error' => 'Email and registration (vrm) required'], 400);
        }
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        if ($token && $chatId) {
            $msg = "🔔 *MOT Reminder signup*\n📧 {$email}\n🚗 VRM: " . strtoupper((string) $vrm);
            if ($motDue = $request->input('motDueDate')) {
                $msg .= "\n📅 MOT due: {$motDue}";
            }
            try {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $msg,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return response()->json(['ok' => true, 'message' => "We'll notify you when your MOT is due."]);
    }

    private function stripe(): ?StripeClient
    {
        $secret = config('services.stripe.secret');
        return $secret ? new StripeClient($secret) : null;
    }

    private function slotsPath(): string
    {
        $dir = storage_path('app');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir . '/booking-slots.json';
    }

    private function readSlots(): array
    {
        $path = $this->slotsPath();
        if (!File::exists($path)) {
            return [];
        }
        $data = json_decode(File::get($path), true);
        return $data['slots'] ?? [];
    }

    private function saveSlot(string $date, string $time): void
    {
        $slots = $this->readSlots();
        $slots[$date] = $slots[$date] ?? [];
        if (!in_array($time, $slots[$date], true)) {
            $slots[$date][] = $time;
            sort($slots[$date]);
        }
        File::put($this->slotsPath(), json_encode(['slots' => $slots], JSON_PRETTY_PRINT));
    }

    private function notifyAndEmail(array $m, string $email): void
    {
        $data = [
            'bookingId' => $m['bookingId'] ?? 'N05-' . time(),
            'customerName' => $m['customerName'] ?? 'Customer',
            'customerEmail' => $email,
            'customerPhone' => $m['customerPhone'] ?? '-',
            'vehicleRegistration' => $m['vehicleRegistration'] ?? '-',
            'vehicleMake' => $m['vehicleMake'] ?? '-',
            'vehicleModel' => $m['vehicleModel'] ?? '',
            'appointmentDate' => $m['appointmentDate'] ?? '-',
            'appointmentTime' => $m['appointmentTime'] ?? '-',
            'serviceType' => $m['serviceType'] ?? 'MOT Test',
            'totalAmount' => $m['totalAmount'] ?? '19.00',
        ];
        $date = $data['appointmentDate'];
        $time = $data['appointmentTime'];
        if ($date !== '-' && $time !== '-') {
            $this->saveSlot($date, $time);
            Booking::updateOrCreate(
                ['booking_id' => $data['bookingId']],
                [
                    'customer_name' => $data['customerName'],
                    'customer_email' => $data['customerEmail'],
                    'customer_phone' => $data['customerPhone'] !== '-' ? $data['customerPhone'] : null,
                    'vehicle_registration' => $data['vehicleRegistration'] !== '-' ? $data['vehicleRegistration'] : '',
                    'vehicle_make' => !empty($data['vehicleMake']) && $data['vehicleMake'] !== '-' ? $data['vehicleMake'] : null,
                    'vehicle_model' => $data['vehicleModel'] ?? null,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'service_type' => $data['serviceType'] ?? null,
                    'total_amount' => $data['totalAmount'] ?? null,
                ]
            );
        }
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        if ($token && $chatId) {
            $msg = "🛞 *New MOT Booking*\n\n📋 *Booking ID:* {$data['bookingId']}\n👤 *Customer:* {$data['customerName']}\n📧 *Email:* {$data['customerEmail']}\n📱 *Phone:* {$data['customerPhone']}\n\n🚗 *Vehicle:* {$data['vehicleMake']} {$data['vehicleModel']}\n🔢 *Registration:* {$data['vehicleRegistration']}\n\n📅 *Date:* {$data['appointmentDate']}\n🕐 *Time:* {$data['appointmentTime']}\n🔧 *Service:* {$data['serviceType']}\n💰 *Amount:* £{$data['totalAmount']}";
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown',
            ]);
        }
        try {
            $adminEmail = config('mail.admin_email');
            if ($adminEmail) {
                Mail::html($this->adminEmailHtml($data), fn ($mail) => $mail
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($adminEmail)
                    ->subject("New booking: {$data['bookingId']} — {$data['customerName']}"));
            }
            $html = $this->customerEmailHtml($data);
            Mail::html($html, fn ($mail) => $mail
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->to($email)
                ->subject("MOT Booking Confirmed - {$data['bookingId']}"));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Booking email failed (booking still saved)', [
                'bookingId' => $data['bookingId'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function adminEmailHtml(array $d): string
    {
        return "<h2>New MOT/Service Booking</h2><table style='border-collapse:collapse;'><tr><td style='padding:8px;border:1px solid #eee;'><strong>Booking ID</strong></td><td>{$d['bookingId']}</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Customer</strong></td><td>{$d['customerName']}</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Email</strong></td><td>{$d['customerEmail']}</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Phone</strong></td><td>{$d['customerPhone']}</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Vehicle</strong></td><td>{$d['vehicleMake']} {$d['vehicleModel']} ({$d['vehicleRegistration']})</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Date & Time</strong></td><td>{$d['appointmentDate']} at {$d['appointmentTime']}</td></tr><tr><td style='padding:8px;border:1px solid #eee;'><strong>Service</strong></td><td>{$d['serviceType']}</td></tr></table>";
    }

    private function customerEmailHtml(array $d): string
    {
        $tpl = resource_path('views/emails/booking-confirmation.blade.php');
        if (File::exists($tpl)) {
            $html = File::get($tpl);
            foreach ($d as $k => $v) {
                $html = str_replace('{{' . $k . '}}', (string) $v, $html);
            }
            return $html;
        }
        return $this->adminEmailHtml($d);
    }
}
