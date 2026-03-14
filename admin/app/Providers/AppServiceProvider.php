<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $mailDriver = SiteSetting::where('key', 'mail_driver')->value('value') ?: 'smtp';
            $resendKey = SiteSetting::where('key', 'mail_resend_api_key')->value('value');

            if ($mailDriver === 'resend' && !empty($resendKey)) {
                Config::set('mail.default', 'resend');
                Config::set('services.resend.key', $resendKey);
                $fromAddr = SiteSetting::where('key', 'mail_from_address')->value('value');
                if ($fromAddr) {
                    Config::set('mail.from.address', $fromAddr);
                }
                $fromName = SiteSetting::where('key', 'mail_from_name')->value('value');
                if ($fromName) {
                    Config::set('mail.from.name', $fromName);
                }
            } else {
                $mailHost = SiteSetting::where('key', 'mail_host')->value('value');
                if (!empty($mailHost)) {
                    Config::set('mail.default', 'smtp');
                    Config::set('mail.mailers.smtp.host', $mailHost);
                    $port = SiteSetting::where('key', 'mail_port')->value('value');
                    if ($port !== null && $port !== '') {
                        $port = (int) $port;
                        Config::set('mail.mailers.smtp.port', $port);
                    }
                    $username = SiteSetting::where('key', 'mail_username')->value('value');
                    if ($username !== null && $username !== '') {
                        Config::set('mail.mailers.smtp.username', $username);
                        Config::set('mail.mailers.smtp.password', SiteSetting::where('key', 'mail_password')->value('value') ?? '');
                    }
                    $encryption = SiteSetting::where('key', 'mail_encryption')->value('value');
                    if ($encryption !== null && $encryption !== '') {
                        Config::set('mail.mailers.smtp.encryption', $encryption);
                    } else {
                        Config::set('mail.mailers.smtp.encryption', ($port ?? 587) == 465 ? 'ssl' : 'tls');
                    }
                    $fromAddr = SiteSetting::where('key', 'mail_from_address')->value('value');
                    if ($fromAddr) {
                        Config::set('mail.from.address', $fromAddr);
                    }
                    $fromName = SiteSetting::where('key', 'mail_from_name')->value('value');
                    if ($fromName) {
                        Config::set('mail.from.name', $fromName);
                    }
                }
            }
            $adminEmail = SiteSetting::where('key', 'admin_email')->value('value');
            if (!empty($adminEmail)) {
                Config::set('mail.admin_email', $adminEmail);
            }

            $telegramToken = SiteSetting::where('key', 'telegram_bot_token')->value('value');
            $telegramChatId = SiteSetting::where('key', 'telegram_chat_id')->value('value');
            if (!empty($telegramToken)) {
                Config::set('services.telegram.bot_token', $telegramToken);
            }
            if (!empty($telegramChatId)) {
                Config::set('services.telegram.chat_id', $telegramChatId);
            }
        } catch (\Throwable $e) {
            // Ignore if site_settings table doesn't exist yet (e.g. during migrations)
        }
    }
}
