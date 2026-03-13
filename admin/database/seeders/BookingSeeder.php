<?php

namespace Database\Seeders;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $base = Carbon::today();
        $dummies = [
            [
                'booking_id' => 'N05-dummy-1',
                'customer_name' => 'John Smith',
                'customer_email' => 'john.smith@example.com',
                'customer_phone' => '07895 123456',
                'vehicle_registration' => 'AB12 CDE',
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Corolla',
                'service_type' => 'MOT + Full Service',
                'total_amount' => 190.00,
            ],
            [
                'booking_id' => 'N05-dummy-2',
                'customer_name' => 'Sarah Jones',
                'customer_email' => 'sarah.jones@example.com',
                'customer_phone' => '07912 654321',
                'vehicle_registration' => 'XY98 ZAB',
                'vehicle_make' => 'Ford',
                'vehicle_model' => 'Focus',
                'service_type' => 'MOT Test',
                'total_amount' => 54.85,
            ],
            [
                'booking_id' => 'N05-dummy-3',
                'customer_name' => 'Mike Wilson',
                'customer_email' => 'mike.wilson@example.com',
                'customer_phone' => '07700 900123',
                'vehicle_registration' => 'GK17 NOP',
                'vehicle_make' => 'Honda',
                'vehicle_model' => 'Civic',
                'service_type' => 'MOT + Major Service',
                'total_amount' => 249.00,
            ],
        ];

        $slots = ['08:00', '09:30', '11:00', '14:00', '15:30'];
        foreach ($dummies as $i => $d) {
            $date = $base->copy()->addDays($i);
            if ($date->isSunday()) {
                $date->addDay();
            }
            Booking::updateOrCreate(
                ['booking_id' => $d['booking_id']],
                array_merge($d, [
                    'appointment_date' => $date,
                    'appointment_time' => $slots[$i % count($slots)],
                ])
            );
        }
    }
}
