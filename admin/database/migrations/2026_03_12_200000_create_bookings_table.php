<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id', 64)->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('vehicle_registration');
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->date('appointment_date');
            $table->string('appointment_time', 10);
            $table->string('service_type')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'appointment_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
