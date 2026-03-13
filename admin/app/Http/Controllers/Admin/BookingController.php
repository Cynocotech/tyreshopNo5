<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $query->where('appointment_date', '>=', $rangeStart);
        }
        if ($rangeEnd) {
            $query->where('appointment_date', '<=', $rangeEnd);
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
}
