<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EposController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SmsMarketingController;
use App\Http\Controllers\Api\BookingController as ApiBookingController;
use App\Http\Controllers\Api\ServicesController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public API under /admin/api (cPanel setups often only route /admin to Laravel)
Route::prefix('admin')->group(function () {
    Route::get('api/vehicle/lookup', [VehicleController::class, 'lookup']);
    Route::get('api/booking/available-slots', [ApiBookingController::class, 'availableSlots']);
    Route::get('api/booking/config', [ApiBookingController::class, 'config']);
    Route::post('api/booking/create-checkout-session', [ApiBookingController::class, 'createCheckoutSession']);
    Route::post('api/booking/confirm-booking', [ApiBookingController::class, 'confirmBooking']);
    Route::post('api/booking/mot-notify', [ApiBookingController::class, 'motNotify']);
    Route::post('api/booking/webhook/stripe', [ApiBookingController::class, 'stripeWebhook']);
});

// Front page (serves index.html at /) — always from repo, no cache
Route::get('/', function () {
    $path = public_path('index.html');
    if (!file_exists($path)) return redirect()->route('login');
    return response()->file($path, [
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
});

// Services JSON for front (read from DB)
Route::get('/data/services.json', [ServicesController::class, 'index']);

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, '__invoke'])->name('dashboard');
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::get('/export', [ExportController::class, '__invoke'])->name('export')->middleware('throttle:10,1');
    Route::resource('services', ServiceController::class)->names('services');
    Route::post('/services/bulk-delete', [ServiceController::class, 'destroyBulk'])->name('services.bulk-delete');
    Route::resource('products', ProductController::class)->names('products');
    Route::resource('product-categories', ProductCategoryController::class)->names('product-categories')->except('show');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/products/add-stock-by-scan', [ProductController::class, 'addStockByScan'])->name('products.add-stock-by-scan');
    Route::get('/epos', [EposController::class, 'index'])->name('epos.index');
    Route::post('/epos/lookup', [EposController::class, 'lookup'])->name('epos.lookup');
    Route::post('/epos/lookup-booking', [EposController::class, 'lookupByBooking'])->name('epos.lookup-by-booking');
    Route::post('/epos/add-serial', [EposController::class, 'addSerial'])->name('epos.add-serial');
    Route::post('/epos/complete', [EposController::class, 'complete'])->name('epos.complete');
    Route::post('/epos/create-card-payment', [EposController::class, 'createCardPayment'])->name('epos.create-card-payment');
    Route::get('/epos/card-payment-status', [EposController::class, 'cardPaymentStatus'])->name('epos.card-payment-status');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales/bulk-delete', [SalesController::class, 'destroyBulk'])->name('sales.bulk-delete');
    Route::post('/sales/bulk-delete-by-dates', [SalesController::class, 'destroyBulkByDates'])->name('sales.bulk-delete-by-dates');
    Route::get('/sales/journal', [SalesController::class, 'journal'])->name('sales.journal');
    Route::get('/sales/daily', [SalesController::class, 'dailySummary'])->name('sales.daily');
    Route::get('/sales/export', [SalesController::class, 'exportCsv'])->name('sales.export');
    Route::get('/sales/{sale}/receipt', [EposController::class, 'receipt'])->name('sales.receipt');
    Route::resource('categories', ServiceCategoryController::class)->parameters(['categories' => 'category'])->names('categories');
    Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [SiteSettingController::class, 'sendTestEmail'])->name('settings.test-email')->middleware('throttle:5,1');
    Route::post('/settings/test-template', [SiteSettingController::class, 'sendTestTemplate'])->name('settings.test-template')->middleware('throttle:5,1');
    Route::post('/settings/test-telegram', [SiteSettingController::class, 'sendTestTelegram'])->name('settings.test-telegram')->middleware('throttle:5,1');
    Route::resource('faqs', FaqController::class)->names('faqs')->except('show');
    Route::resource('areas', AreaController::class)->names('areas')->except('show');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/attend', [AdminBookingController::class, 'markAttended'])->name('bookings.attend');
    Route::post('/bookings/bulk-delete', [AdminBookingController::class, 'destroyBulk'])->name('bookings.bulk-delete');
    Route::get('/bookings/canceled', [AdminBookingController::class, 'canceled'])->name('bookings.canceled');
    Route::get('/bookings/attended', [AdminBookingController::class, 'attended'])->name('bookings.attended');
    Route::get('/bookings/list', [AdminBookingController::class, 'list'])->name('bookings.list');
    Route::get('/bookings/{booking}/invoice', [AdminBookingController::class, 'invoice'])->name('bookings.invoice');
    Route::get('/sms-marketing', [SmsMarketingController::class, 'index'])->name('sms-marketing.index');
    Route::post('/sms-marketing/send', [SmsMarketingController::class, 'send'])->name('sms-marketing.send');
    Route::post('/sms-marketing/send-csv', [SmsMarketingController::class, 'sendCsv'])->name('sms-marketing.send-csv');
});

require __DIR__.'/auth.php';
