<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServicesController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/vehicle/lookup', [VehicleController::class, 'lookup']);
Route::get('/booking/available-slots', [BookingController::class, 'availableSlots']);
Route::get('/booking/config', [BookingController::class, 'config']);
Route::post('/booking/create-checkout-session', [BookingController::class, 'createCheckoutSession']);
Route::post('/booking/confirm-booking', [BookingController::class, 'confirmBooking']);
Route::post('/booking/mot-notify', [BookingController::class, 'motNotify']);
Route::post('/booking/webhook/stripe', [BookingController::class, 'stripeWebhook']);
