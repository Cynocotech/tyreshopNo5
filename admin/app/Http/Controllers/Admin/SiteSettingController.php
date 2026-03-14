<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::orderBy('key')->get()->pluck('value', 'key');
        $activeTab = request('tab', old('_tab', 'general'));
        return view('admin.settings.index', ['settings' => $settings->toArray(), 'activeTab' => $activeTab]);
    }

    public function update(Request $request): RedirectResponse
    {
        $allowed = [
            'site_name', 'site_description', 'address_street', 'address_locality', 'address_region', 'address_postcode', 'address_country',
            'phone', 'phone_international', 'email', 'logo_url', 'logo_link', 'url',
            'opening_days', 'opening_time', 'closing_time', 'opening_hours_display',
            'tagline', 'footer_tagline', 'footer_description', 'copyright',
            'hero_book_price', 'hero_save', 'footer_mot_price', 'areas_intro',
            'footer_offer_title', 'footer_offer_subtitle', 'footer_offer_label',
            'footer_offer_was_price', 'footer_offer_save', 'footer_offer_feature',
            'footer_offer_btn_text', 'footer_offer_disclaimer',
            'combo_section_title', 'combo_section_intro', 'combo_combined_desc',
            'show_update_notice',
            'payment_card_provider', 'payment_stripe_secret', 'payment_stripe_publishable', 'payment_stripe_reader_id', 'payment_stripe_enabled',
            'payment_teya_api_url', 'payment_teya_merchant_id', 'payment_teya_api_key', 'payment_teya_enabled',
            'payment_faster_api_url', 'payment_faster_client_id', 'payment_faster_client_secret', 'payment_faster_enabled',
            'payment_lfat_api_url', 'payment_lfat_api_key', 'payment_lfat_enabled',
            'vrn_api_key',
            'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption',
            'mail_from_address', 'mail_from_name', 'admin_email',
        ];
        $checkboxKeys = ['payment_stripe_enabled', 'payment_teya_enabled', 'payment_faster_enabled', 'payment_lfat_enabled', 'show_update_notice'];
        foreach ($checkboxKeys as $key) {
            if (in_array($key, $allowed)) {
                $value = in_array($request->input($key), ['1', 1], true) ? '1' : '0';
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
        foreach ($request->all() as $key => $value) {
            if (!in_array($key, $allowed) || in_array($key, $checkboxKeys)) {
                continue;
            }
            if ($value === null) {
                continue;
            }
            // Don't overwrite mail_password when left blank (keep existing)
            if ($key === 'mail_password' && (string) $value === '') {
                continue;
            }
            if (in_array($key, ['payment_card_provider'])) {
                $value = (string) $value;
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }
        Cache::forget('site_settings');
        $tab = $request->input('_tab', 'general');
        return redirect()->route('admin.settings.index', ['tab' => $tab])->with('success', 'Settings saved.');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $request->validate(['test_email' => 'required|email']);
        $to = $request->input('test_email');

        try {
            Mail::html('<p>This is a test email from <strong>NO5 Tyre & MOT</strong> admin.</p><p>If you received this, your SMTP settings are configured correctly.</p>', fn ($mail) => $mail
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->to($to)
                ->subject('Test email — NO5 Tyre & MOT'));
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('test_email_error', $e->getMessage());
        }

        return redirect()->route('admin.settings.index', ['tab' => 'email'])
            ->with('test_email_sent', $to);
    }
}
