<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class ServicesController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::orderBy('sort_order')->get();
        $categoriesOut = [];
        foreach ($categories as $c) {
            $categoriesOut[$c->slug] = [
                'label' => $c->label,
                'sortOrder' => $c->sort_order,
            ];
        }

        $services = Service::with('category')->orderBy('sort_order')->orderBy('title')->get();
        $servicesOut = [];
        foreach ($services as $s) {
            $servicesOut[] = [
                'id' => $s->slug,
                'value' => $s->value,
                'title' => $s->title,
                'icon' => $s->icon,
                'price' => (float) $s->price,
                'heroMOTPrice' => $s->hero_mot_price ? (float) $s->hero_mot_price : null,
                'priceLabel' => $s->price_label,
                'priceDisplay' => $s->price_display,
                'category' => $s->category?->slug ?? '',
                'isQuote' => $s->is_quote,
                'keywords' => $s->keywords ?? [],
            ];
        }

        $settings = [
            'hero_book_price' => SiteSetting::get('hero_book_price'),
            'hero_save' => SiteSetting::get('hero_save'),
            'footer_mot_price' => SiteSetting::get('footer_mot_price'),
            'opening_hours_display' => SiteSetting::get('opening_hours_display'),
            'show_update_notice' => SiteSetting::get('show_update_notice', '1'),
        ];

        return response()->json([
            'services' => $servicesOut,
            'categories' => $categoriesOut,
            'settings' => $settings,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
