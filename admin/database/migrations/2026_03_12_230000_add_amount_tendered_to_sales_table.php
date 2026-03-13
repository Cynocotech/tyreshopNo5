<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'amount_tendered')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('amount_tendered', 10, 2)->nullable()->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'amount_tendered')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('amount_tendered');
            });
        }
    }
};
