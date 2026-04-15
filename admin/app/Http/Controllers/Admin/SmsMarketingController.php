<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\SmsCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SmsMarketingController extends Controller
{
    public function index(): View
    {
        $campaigns = SmsCampaign::orderByDesc('created_at')->get();
        return view('admin.sms-marketing.index', compact('campaigns'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipients'    => 'required|string',
            'message'       => 'required|string|max:160',
            'campaign_name' => 'nullable|string|max:255',
        ]);

        $recipients = $this->parseRecipients($validated['recipients']);
        if (empty($recipients)) {
            return redirect()->route('admin.sms-marketing.index')->with('error', 'No valid phone numbers found.');
        }

        [$apiKey, $sender] = $this->credentials();
        if (!$apiKey || !$sender) {
            return redirect()->route('admin.sms-marketing.index')
                ->with('error', 'SMS not configured. Add VoodooSMS credentials in Site Settings → APIs.');
        }

        $campaignName = $validated['campaign_name'] ?? 'Manual – ' . now()->format('d M Y H:i');
        $campaign = SmsCampaign::create([
            'name'             => $campaignName,
            'message'          => $validated['message'],
            'source'           => 'manual',
            'total_recipients' => count($recipients),
        ]);

        $sent = 0; $failed = 0;
        foreach ($recipients as $to) {
            static::sendViaSms($apiKey, $sender, $to, $validated['message']) ? $sent++ : $failed++;
        }

        $campaign->update(['sent_count' => $sent, 'failed_count' => $failed]);

        return redirect()->route('admin.sms-marketing.index')
            ->with('success', "Sent {$sent} of " . count($recipients) . " message(s).");
    }

    public function sendCsv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv'           => 'required|file|mimes:csv,txt|max:2048',
            'message'       => 'required|string|max:160',
            'campaign_name' => 'nullable|string|max:255',
        ]);

        $content = file_get_contents($validated['csv']->getRealPath());
        $lines   = array_map('str_getcsv', explode("\n", $content));
        $headers = array_map('strtolower', array_map('trim', $lines[0] ?? []));
        $phoneCol = null;
        foreach (['phone', 'mobile', 'number', 'customer_phone', 'tel'] as $col) {
            if (in_array($col, $headers)) { $phoneCol = array_search($col, $headers); break; }
        }
        if ($phoneCol === null) $phoneCol = 0;

        $recipients = [];
        for ($i = 1; $i < count($lines); $i++) {
            $row = $lines[$i] ?? [];
            $n   = preg_replace('/\D/', '', trim($row[$phoneCol] ?? ''));
            if (strlen($n) >= 10) {
                $recipients[] = $this->normaliseUk($n);
            }
        }
        $recipients = array_unique(array_filter($recipients));

        if (empty($recipients)) {
            return redirect()->route('admin.sms-marketing.index')->with('error', 'No valid phone numbers found in CSV.');
        }

        [$apiKey, $sender] = $this->credentials();
        if (!$apiKey || !$sender) {
            return redirect()->route('admin.sms-marketing.index')
                ->with('error', 'SMS not configured. Add VoodooSMS credentials in Site Settings → APIs.');
        }

        $campaignName = $validated['campaign_name'] ?? 'CSV – ' . now()->format('d M Y H:i');
        $campaign = SmsCampaign::create([
            'name'             => $campaignName,
            'message'          => $validated['message'],
            'source'           => 'csv',
            'total_recipients' => count($recipients),
        ]);

        $sent = 0; $failed = 0;
        foreach ($recipients as $to) {
            static::sendViaSms($apiKey, $sender, $to, $validated['message']) ? $sent++ : $failed++;
        }

        $campaign->update(['sent_count' => $sent, 'failed_count' => $failed]);

        return redirect()->route('admin.sms-marketing.index')
            ->with('success', "Sent {$sent} of " . count($recipients) . " message(s) from CSV.");
    }

    // ── Shared SMS helper (also used by BookingController) ──────────────────

    public static function sendViaSms(string $apiKey, string $sender, string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.voodoosms.com/sendsms', [
                'to'   => $to,
                'from' => $sender,
                'msg'  => $message,
            ]);

            $body = $response->json();
            $status = $body['messages'][0]['status'] ?? '';
            if ($response->successful() && in_array($status, ['PENDING_SENT', 'SENT', 'DELIVERED'], true)) {
                return true;
            }
            Log::warning('VoodooSMS send failed', ['to' => $to, 'status' => $response->status(), 'body' => $body]);
            return false;
        } catch (\Throwable $e) {
            Log::error('VoodooSMS error', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public static function credentialsStatic(): array
    {
        $apiKey = config('services.voodoo.api_key') ?: SiteSetting::get('voodoo_api_key');
        $sender = config('services.voodoo.sender')  ?: SiteSetting::get('voodoo_sender', 'NO5Tyres');
        return [$apiKey, $sender];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function credentials(): array
    {
        return static::credentialsStatic();
    }

    private function parseRecipients(string $input): array
    {
        $numbers = [];
        foreach (array_filter(array_map('trim', explode("\n", $input))) as $line) {
            foreach (preg_split('/[\s,;]+/', $line) as $p) {
                $n = preg_replace('/\D/', '', $p);
                if (strlen($n) >= 10) $numbers[] = $this->normaliseUk($n);
            }
        }
        return array_unique(array_filter($numbers));
    }

    private function normaliseUk(string $n): string
    {
        if (str_starts_with($n, '0'))  return '44' . substr($n, 1);
        if (!str_starts_with($n, '44')) return '44' . $n;
        return $n;
    }
}
