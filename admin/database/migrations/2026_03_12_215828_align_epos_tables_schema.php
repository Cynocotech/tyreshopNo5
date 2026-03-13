<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('product_serials', 'sold_at')) {
            Schema::table('product_serials', function (Blueprint $table) {
                $table->timestamp('sold_at')->nullable()->after('sold');
            });
        }
        if (!Schema::hasColumn('sales', 'completed_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('total');
            });
        }
        if (Schema::hasColumn('sale_items', 'total_price')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->renameColumn('total_price', 'total');
            });
        } elseif (!Schema::hasColumn('sale_items', 'total')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->decimal('total', 10, 2)->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropColumn('sold_at');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('total_price', 10, 2)->nullable();
        });
        \DB::statement('UPDATE sale_items SET total_price = total');
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
