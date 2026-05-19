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
                'features' => $s->features ?? [],
                'comboBadge' => $s->combo_badge,
                'comboSubtitle' => $s->combo_subtitle,
                'comboFeatures' => $s->combo_features ?? [],
                'comboSaving' => $s->combo_saving,
                'isComboHot' => (bool) $s->is_combo_hot,
                'comboDisplayPrice' => $s->combo_display_price,
            ];
        }

        $settings = [
            'site_name' => SiteSetting::get('site_name', 'Bourn Hill Tyre & MOT | London'),
            'site_description' => SiteSetting::get('site_description', 'MOT testing, tyre fitting, puncture repairs, wheel alignment and car servicing in London.'),
            'seo_title' => SiteSetting::get('seo_title', 'Bourn Hill Tyre & MOT | London Tyres, MOT Testing & Car Servicing'),
            'seo_description' => SiteSetting::get('seo_description', 'Book Bourn Hill Tyre & MOT in London for MOT testing, new tyres, puncture repair, wheel alignment, brakes, diagnostics and car servicing.'),
            'seo_keywords' => SiteSetting::get('seo_keywords', 'Bourn Hill Tyre & MOT London, tyres near me, MOT near me, MOT test London, tyre fitting London, puncture repair London, car servicing London, wheel alignment London'),
            'address_street' => SiteSetting::get('address_street', '6a Bourne Hill'),
            'address_locality' => SiteSetting::get('address_locality', 'Palmers Green'),
            'address_region' => SiteSetting::get('address_region', ''),
            'address_postcode' => SiteSetting::get('address_postcode', 'N13 4LG'),
            'address_country' => SiteSetting::get('address_country', 'GB'),
            'phone' => SiteSetting::get('phone', '07895 859505'),
            'phone_international' => SiteSetting::get('phone_international', '+447895859505'),
            'email' => SiteSetting::get('email', 'info@no5mot.co.uk'),
            'url' => SiteSetting::get('url', 'https://no5mot.co.uk'),
            'logo_url' => SiteSetting::get('logo_url', '/images/logo.png'),
            'hero_image_url' => SiteSetting::get('hero_image_url', '/images/hero-garage.jpg'),
            'tagline' => SiteSetting::get('tagline', 'Bourne Hill · London'),
            'footer_tagline' => SiteSetting::get('footer_tagline', ''),
            'footer_description' => SiteSetting::get('footer_description', "London's trusted tyre and MOT specialist for MOT testing, tyres, puncture repairs, brakes, diagnostics and servicing."),
            'copyright' => SiteSetting::get('copyright', '© 2026 Bourn Hill Tyre & MOT | London. All rights reserved.'),
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
            'footer_offer_btn' => SiteSetting::get('footer_offer_btn') ?? SiteSetting::get('footer_offer_btn_text', 'Book Now →'),
            'footer_offer_disclaimer' => SiteSetting::get('footer_offer_disclaimer', '*New bookings only. Excludes commercial vehicles.'),
            'combo_section_title' => SiteSetting::get('combo_section_title', 'Special Offer'),
            'combo_section_intro' => SiteSetting::get('combo_section_intro', 'Book your MOT together with a service and pay just £19 — saving at least £31.'),
            'combo_combined_desc' => SiteSetting::get('combo_combined_desc', 'MOT Test + Service combined'),
        ];

        if (count($servicesOut) === 0) {
            $path = base_path('../data/services.json');
            if (is_readable($path)) {
                $file = json_decode((string) file_get_contents($path), true);
                if (is_array($file) && ! empty($file['services'])) {
                    $servicesOut = $file['services'];
                    if (! empty($file['categories']) && is_array($file['categories'])) {
                        $categoriesOut = [];
                        foreach ($file['categories'] as $slug => $cat) {
                            if (! is_array($cat)) {
                                continue;
                            }
                            $categoriesOut[$slug] = [
                                'label' => $cat['label'] ?? $slug,
                                'sortOrder' => (int) ($cat['sortOrder'] ?? 0),
                            ];
                        }
                    }
                    $fromFile = $file['settings'] ?? [];
                    if (is_array($fromFile)) {
                        foreach ($fromFile as $key => $val) {
                            if ($val !== null && $val !== '') {
                                $settings[$key] = $val;
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'services' => $servicesOut,
            'categories' => $categoriesOut,
            'settings' => $settings,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
