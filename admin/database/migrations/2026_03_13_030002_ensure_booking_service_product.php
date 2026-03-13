<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('products')->where('sku', 'BOOKING-SVC')->exists();
        if (!$exists) {
            DB::table('products')->insert([
                'name' => 'Booking Service',
                'sku' => 'BOOKING-SVC',
                'barcode' => null,
                'price' => 0,
                'quantity' => 99999,
                'low_stock_threshold' => 99999,
                'requires_serial' => false,
                'is_active' => true,
                'sort_order' => 9999,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('products')->where('sku', 'BOOKING-SVC')->delete();
    }
};
