<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'N05 Tyre & MOT Service',
            'site_description' => 'MOT testing, car servicing, tyre fitting in Palmers Green, North London.',
            'address_street' => '6A Bourne Hill',
            'address_locality' => 'Southgate',
            'address_region' => 'London',
            'address_postcode' => 'N13 4LG',
            'address_country' => 'GB',
            'phone' => '07895 859505',
            'phone_international' => '+447895859505',
            'email' => 'info@no5mot.co.uk',
            'logo_url' => 'https://no5tyreandmot.co.uk/wp-content/uploads/2025/12/logo.png',
            'url' => 'https://no5mot.co.uk',
            'opening_days' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'opening_time' => '08:00',
            'closing_time' => '18:00',
            'opening_hours_display' => 'Mon–Sat: 8am–6pm',
            'tagline' => 'Palmers Green · North London',
            'footer_tagline' => 'Formerly Palmers Green Tyres',
            'footer_description' => "North London's most trusted tyre and MOT specialist. Excellent-rated on Google with 500+ reviews. Serving the area since 2008.",
            'copyright' => '© 2026 N05 Tyre and MOT Service Ltd. Formerly Palmers Green Tyres. All rights reserved.',
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
            'show_maintenance_notice' => '0',
            'maintenance_message' => "Site maintenance — we'll be back shortly",
        ];
        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
