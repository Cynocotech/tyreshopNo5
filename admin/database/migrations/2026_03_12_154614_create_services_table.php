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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('value');
            $table->string('title');
            $table->string('icon')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('hero_mot_price', 10, 2)->nullable();
            $table->string('price_label');
            $table->string('price_display');
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_quote')->default(false);
            $table->json('keywords')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
