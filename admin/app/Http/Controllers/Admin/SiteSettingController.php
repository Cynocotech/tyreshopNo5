<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            'vrn_api_key', 'telegram_bot_token', 'telegram_chat_id',
            'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption',
            'mail_resend_api_key', 'mail_from_address', 'mail_from_name', 'admin_email',
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
            // Don't overwrite sensitive fields when left blank (keep existing)
            if (in_array($key, ['mail_password', 'mail_resend_api_key', 'telegram_bot_token']) && (string) $value === '') {
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
            Mail::html(
                '<p>This is a test email from <strong>NO5 Tyre & MOT</strong> admin.</p><p>If you received this, your email settings are configured correctly.</p><p><small>Sent at ' . now()->format('Y-m-d H:i:s') . ' UTC</small></p>',
                fn ($mail) => $mail
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($to)
                    ->subject('Test email — NO5 Tyre & MOT')
            );
            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('success', 'Test email sent to ' . $to . '. Check inbox and spam folder.')
                ->with('test_email_sent', $to);
        } catch (\Throwable $e) {
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $user = config('mail.mailers.smtp.username');
            Log::error('Admin test email failed', [
                'to' => $to,
                'host' => $host,
                'port' => $port,
                'username' => $user ?: '(none)',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = $e->getMessage();
            $hint = '';
            if (str_contains(strtolower($msg), 'connection') || str_contains($msg, 'refused') || str_contains($msg, 'timed out')) {
                $hint = ' Check host, port, and firewall. Try port 587 (TLS) or 465 (SSL).';
            } elseif (str_contains(strtolower($msg), 'authenticat') || str_contains($msg, '535') || str_contains(strtolower($msg), 'auth')) {
                $hint = ' Check username and password. Gmail/Outlook often require app passwords.';
            } elseif (str_contains(strtolower($msg), 'ssl') || str_contains(strtolower($msg), 'tls') || str_contains(strtolower($msg), 'certificate')) {
                $hint = ' Try switching Encryption: port 587= TLS, 465= SSL, 25= None.';
            }
            $driver = config('mail.default');
            $configLine = $driver === 'resend' ? ' Using Resend API.' : ($host ? " Using: {$host}:{$port}" : ' No mail configured. Save settings first.');
            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('error', 'Mail failed' . $configLine . ' — ' . $msg . $hint)
                ->with('test_email_error', $msg);
        }
    }

    public function sendTestTelegram(): RedirectResponse
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            return redirect()->route('admin.settings.index', ['tab' => 'apis'])
                ->with('error', 'Telegram not configured. Save Bot Token and Chat ID first.')
                ->with('test_telegram_error', 'Missing bot_token or chat_id');
        }

        try {
            $msg = "🛞 *Telegram test — NO5 Tyre & MOT*\n\n✓ Your bot is configured correctly.\n\n_" . now()->format('Y-m-d H:i:s') . " UTC_";
            $res = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown',
            ]);

            if (!$res->successful()) {
                $body = $res->json();
                $err = $body['description'] ?? $res->body() ?: $res->reason();
                throw new \RuntimeException($err);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'apis'])
                ->with('success', 'Test message sent to Telegram. Check your chat.')
                ->with('test_telegram_sent', true);
        } catch (\Throwable $e) {
            Log::warning('Admin test Telegram failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.settings.index', ['tab' => 'apis'])
                ->with('error', 'Telegram failed — ' . $e->getMessage())
                ->with('test_telegram_error', $e->getMessage());
        }
    }
}
