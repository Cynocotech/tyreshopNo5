<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $business = [
            'name' => SiteSetting::get('site_name', 'N05 Tyre & MOT Service'),
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
        $services = Service::orderBy('sort_order')->orderBy('title')->get();
        $openCreate = $request->boolean('new');

        return view('admin.bookings.calendar', compact('business', 'services', 'openCreate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'vehicle_registration' => 'nullable|string|max:20',
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string|max:10',
            'service_type' => 'required|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        $bookingId = 'N05-' . time() . '-' . substr(bin2hex(random_bytes(4)), 0, 4);
        Booking::create([
            'booking_id' => $bookingId,
            'customer_name' => $valid['customer_name'],
            'customer_email' => $valid['customer_email'] ?: 'admin-booked@no5.local',
            'customer_phone' => $valid['customer_phone'],
            'vehicle_registration' => $valid['vehicle_registration'] ?: 'TBC',
            'vehicle_make' => $valid['vehicle_make'],
            'vehicle_model' => $valid['vehicle_model'],
            'appointment_date' => $valid['appointment_date'],
            'appointment_time' => preg_replace('/\s+/', '', $valid['appointment_time']),
            'service_type' => $valid['service_type'],
            'total_amount' => $valid['total_amount'] ?? null,
        ]);

        return redirect()->route('admin.bookings.index')->with('success', "Booking {$bookingId} created.");
    }

    public function list(Request $request): JsonResponse
    {
        $rangeStart = $request->query('start');
        $rangeEnd = $request->query('end');
        $query = Booking::active();
        if ($rangeStart) {
            $date = \Carbon\Carbon::parse($rangeStart)->format('Y-m-d');
            $query->where('appointment_date', '>=', $date);
        }
        if ($rangeEnd) {
            $date = \Carbon\Carbon::parse($rangeEnd)->format('Y-m-d');
            $query->where('appointment_date', '<=', $date);
        }
        $bookings = $query->orderBy('appointment_date')->orderBy('appointment_time')->get();
        $events = [];
        foreach ($bookings as $b) {
            $time = $b->appointment_time ?: '08:00';
            $parts = array_map('intval', explode(':', $time));
            $h = $parts[0] ?? 0;
            $m = ($parts[1] ?? 0) + 30;
            if ($m >= 60) {
                $h++;
                $m -= 60;
            }
            $endTime = sprintf('%02d:%02d', $h, $m);
            $startStr = $b->appointment_date->format('Y-m-d') . 'T' . $time;
            $endStr = $b->appointment_date->format('Y-m-d') . 'T' . $endTime;
            $events[] = [
                'id' => $b->id,
                'title' => trim(($b->vehicle_make ?? '') . ' ' . ($b->vehicle_model ?? '')) ?: $b->vehicle_registration,
                'start' => $startStr,
                'end' => $endStr,
                'extendedProps' => [
                    'bookingId' => $b->booking_id,
                    'customer' => $b->customer_name,
                    'phone' => $b->customer_phone,
                    'email' => $b->customer_email,
                    'vehicle' => $b->vehicle_registration . ' ' . trim(($b->vehicle_make ?? '') . ' ' . ($b->vehicle_model ?? '')),
                    'service' => $b->service_type,
                    'amount' => $b->total_amount ? '£' . number_format((float) $b->total_amount, 2) : null,
                    'attendedAt' => $b->attended_at?->toIso8601String(),
                ],
            ];
        }
        return response()->json($events);
    }

    public function invoice(Booking $booking): View
    {
        $business = [
            'name' => SiteSetting::get('site_name', 'N05 Tyre & MOT Service'),
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
        return view('admin.bookings.invoice', compact('booking', 'business'));
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->isCanceled()) {
            return redirect()->route('admin.bookings.index')->with('error', 'Booking already canceled.');
        }
        $booking->update(['canceled_at' => now()]);
        return redirect()->route('admin.bookings.index')->with('success', "Booking {$booking->booking_id} canceled.");
    }

    public function canceled(Request $request): View
    {
        $bookings = Booking::canceled()->orderByDesc('canceled_at')->paginate(30);
        return view('admin.bookings.canceled', compact('bookings'));
    }

    public function destroyBulk(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:bookings,id'])['ids'];
        $bookings = Booking::whereIn('id', $ids)->whereNotNull('canceled_at')->get();
        $count = $bookings->count();
        foreach ($bookings as $b) {
            $b->delete();
        }
        return redirect()->back()->with('success', "Permanently deleted {$count} canceled booking(s).");
    }

    public function attended(): View
    {
        $bookings = Booking::attended()->orderByDesc('attended_at')->paginate(30);
        return view('admin.bookings.attended', compact('bookings'));
    }

    public function markAttended(Booking $booking): RedirectResponse
    {
        if ($booking->isCanceled()) {
            return redirect()->route('admin.bookings.index')->with('error', 'Cannot mark canceled booking as attended.');
        }
        if ($booking->isAttended()) {
            return redirect()->route('admin.bookings.index')->with('info', 'Booking already marked as attended.');
        }
        $booking->update(['attended_at' => now()]);

        $siteName = SiteSetting::get('site_name', 'NO5 Tyre & MOT');
        $logoUrl = SiteSetting::get('logo_url');
        $siteUrl = SiteSetting::get('url', url('/'));
        $phone = SiteSetting::get('phone');
        $googleReviewUrl = SiteSetting::get('google_review_url', 'https://g.page/r/YOUR_PLACE_ID/review');

        $data = [
            'customerName' => $booking->customer_name ?? 'Customer',
            'bookingId' => $booking->booking_id,
            'serviceType' => $booking->service_type ?? 'Service',
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
            'siteUrl' => $siteUrl,
            'phone' => $phone,
            'googleReviewUrl' => $googleReviewUrl,
        ];

        $html = view('emails.booking-thank-you', $data)->render();
        try {
            Mail::html($html, fn ($mail) => $mail
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->to($booking->customer_email)
                ->subject("Thank you for visiting – Rate us on Google"));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Thank you email failed', ['bookingId' => $booking->booking_id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('admin.bookings.index')->with('success', "Marked {$booking->booking_id} as attended. Thank you email sent to {$booking->customer_email}.");
    }
}
