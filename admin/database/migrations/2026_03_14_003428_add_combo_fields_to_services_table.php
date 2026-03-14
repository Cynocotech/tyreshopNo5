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
        Schema::table('services', function (Blueprint $table) {
            $table->string('combo_badge')->nullable()->after('sort_order');
            $table->string('combo_subtitle')->nullable()->after('combo_badge');
            $table->json('combo_features')->nullable()->after('combo_subtitle');
            $table->string('combo_saving')->nullable()->after('combo_features');
            $table->boolean('is_combo_hot')->default(false)->after('combo_saving');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['combo_badge', 'combo_subtitle', 'combo_features', 'combo_saving', 'is_combo_hot']);
        });
    }
};
