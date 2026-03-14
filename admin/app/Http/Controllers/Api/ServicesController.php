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
                'comboBadge' => $s->combo_badge,
                'comboSubtitle' => $s->combo_subtitle,
                'comboFeatures' => $s->combo_features ?? [],
                'comboSaving' => $s->combo_saving,
                'isComboHot' => (bool) $s->is_combo_hot,
                'comboDisplayPrice' => $s->combo_display_price,
            ];
        }

        $settings = [
            'hero_book_price' => SiteSetting::get('hero_book_price'),
            'hero_save' => SiteSetting::get('hero_save'),
            'footer_mot_price' => SiteSetting::get('footer_mot_price'),
            'opening_hours_display' => SiteSetting::get('opening_hours_display'),
            'show_update_notice' => SiteSetting::get('show_update_notice', '1'),
            'footer_offer_title' => SiteSetting::get('footer_offer_title', "Today's Offer"),
            'footer_offer_subtitle' => SiteSetting::get('footer_offer_subtitle', 'Book Today'),
            'footer_offer_label' => SiteSetting::get('footer_offer_label', 'MOT + Service'),
            'footer_offer_was_price' => SiteSetting::get('footer_offer_was_price', '£50'),
            'footer_offer_save' => SiteSetting::get('footer_offer_save', 'Save £31+'),
            'footer_offer_feature' => SiteSetting::get('footer_offer_feature', '🚗 Free collection & delivery'),
            'footer_offer_btn_text' => SiteSetting::get('footer_offer_btn_text', 'Book Now →'),
            'footer_offer_disclaimer' => SiteSetting::get('footer_offer_disclaimer', '*New bookings only. Excludes commercial vehicles.'),
            'combo_section_title' => SiteSetting::get('combo_section_title', 'Special Offer'),
            'combo_section_intro' => SiteSetting::get('combo_section_intro', 'Book your MOT together with a service and pay just £19 — saving at least £31.'),
            'combo_combined_desc' => SiteSetting::get('combo_combined_desc', 'MOT Test + Service combined'),
        ];

        return response()->json([
            'services' => $servicesOut,
            'categories' => $categoriesOut,
            'settings' => $settings,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
