<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Bourn Hill Tyre & MOT | London',
            'site_description' => 'MOT testing, tyre fitting, puncture repairs, wheel alignment and car servicing in London.',
            'seo_title' => 'Bourn Hill Tyre & MOT | London Tyres, MOT Testing & Car Servicing',
            'seo_description' => 'Book Bourn Hill Tyre & MOT in London for MOT testing, new tyres, puncture repair, wheel alignment, brakes, diagnostics and car servicing.',
            'seo_keywords' => 'Bourn Hill Tyre & MOT London, Bourn Hill Tyre and MOT, tyres near me, MOT near me, MOT test London, tyre fitting London, puncture repair London, car servicing London, wheel alignment London, mobile tyre fitting London, cheap tyres London, emergency tyre replacement',
            'address_street' => '6A Bourne Hill',
            'address_locality' => 'Southgate',
            'address_region' => 'London',
            'address_postcode' => 'N13 4LG',
            'address_country' => 'GB',
            'phone' => '07895 859505',
            'phone_international' => '+447895859505',
            'email' => 'info@no5mot.co.uk',
            'logo_url' => '/images/logo.png',
            'hero_image_url' => '/images/hero-garage.jpg',
            'url' => 'https://no5mot.co.uk',
            'opening_days' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'opening_time' => '08:00',
            'closing_time' => '18:00',
            'opening_hours_display' => 'Mon–Sat: 8am–6pm',
            'tagline' => 'Bourne Hill · London',
            'footer_tagline' => 'Formerly Palmers Green Tyres',
            'footer_description' => "London's trusted tyre and MOT specialist for MOT testing, tyres, puncture repairs, brakes, diagnostics and servicing.",
            'copyright' => '© 2026 Bourn Hill Tyre & MOT | London. All rights reserved.',
            'hero_book_price' => '£19',
            'hero_save' => '£31',
            'footer_mot_price' => '£19',
            'areas_intro' => 'Serving many North London locations — wherever you are in the area.',
            'footer_offer_title' => "Today's Offer",
            'footer_offer_subtitle' => 'Book Today',
            'footer_offer_label' => 'MOT + Service',
            'footer_offer_was_price' => '£50',
            'footer_offer_save' => 'Save £31+',
            'footer_offer_feature' => '🚗 Free collection & delivery',
            'footer_offer_btn_text' => 'Book Now →',
            'footer_offer_disclaimer' => '*New bookings only. Excludes commercial vehicles.',
            'show_update_notice' => '1',
            'combo_section_title' => 'Special Offer',
            'combo_section_intro' => "Book your MOT together with a service and pay just £19 — saving at least £31.",
            'combo_combined_desc' => 'MOT Test + Service combined',
        ];
        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
