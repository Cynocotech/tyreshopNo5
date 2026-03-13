<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportBookingSlotsCommand extends Command
{
    protected $signature = 'bookings:import-slots';

    protected $description = 'Import existing booked slots from booking-slots.json into the bookings table (creates placeholder records)';

    public function handle(): int
    {
        $path = storage_path('app/booking-slots.json');
        if (!File::exists($path)) {
            $this->info('No booking-slots.json found.');
            return 0;
        }
        $data = json_decode(File::get($path), true);
        $slots = $data['slots'] ?? [];
        $count = 0;
        foreach ($slots as $date => $times) {
            if (!is_array($times)) {
                continue;
            }
            foreach ($times as $time) {
                $bookingId = 'N05-import-' . $date . '-' . str_replace(':', '', $time);
                if (Booking::where('booking_id', $bookingId)->exists()) {
                    continue;
                }
                Booking::create([
                    'booking_id' => $bookingId,
                    'customer_name' => 'Imported slot',
                    'customer_email' => 'unknown@import.local',
                    'vehicle_registration' => '-',
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'service_type' => 'MOT Test',
                ]);
                $count++;
            }
        }
        $this->info("Imported {$count} placeholder bookings.");
        return 0;
    }
}
