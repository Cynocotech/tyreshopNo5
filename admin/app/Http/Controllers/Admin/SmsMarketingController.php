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
            'recipients' => 'required|string',
            'message' => 'required|string|max:160',
            'campaign_name' => 'nullable|string|max:255',
        ]);

        $recipients = $this->parseRecipients($validated['recipients']);
        if (empty($recipients)) {
            return redirect()->route('admin.sms-marketing.index')->with('error', 'No valid phone numbers found.');
        }

        $twilioSid = config('services.twilio.sid') ?: SiteSetting::get('twilio_sid');
        $twilioToken = config('services.twilio.token') ?: SiteSetting::get('twilio_token');
        $twilioFrom = config('services.twilio.from') ?: SiteSetting::get('twilio_from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            return redirect()->route('admin.sms-marketing.index')
                ->with('error', 'SMS not configured. Add Twilio credentials in Site Settings → APIs (twilio_sid, twilio_token, twilio_from).');
        }

        $campaignName = $validated['campaign_name'] ?? 'Manual – ' . now()->format('d M Y H:i');
        $campaign = SmsCampaign::create([
            'name' => $campaignName,
            'message' => $validated['message'],
            'source' => 'manual',
            'total_recipients' => count($recipients),
        ]);

        $sent = 0;
        $failed = 0;
        foreach ($recipients as $to) {
            try {
                $response = Http::asForm()->withBasicAuth($twilioSid, $twilioToken)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages", [
                        'To' => $to,
                        'From' => $twilioFrom,
                        'Body' => $validated['message'],
                    ]);
                if ($response->successful()) {
                    $sent++;
                } else {
                    $failed++;
                    Log::warning('SMS send failed', ['to' => $to, 'status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('SMS send error', ['to' => $to, 'error' => $e->getMessage()]);
            }
        }

        $campaign->update(['sent_count' => $sent, 'failed_count' => $failed]);

        return redirect()->route('admin.sms-marketing.index')
            ->with('success', "Sent {$sent} of " . count($recipients) . " message(s).");
    }

    private function parseRecipients(string $input): array
    {
        $numbers = [];
        $lines = array_filter(array_map('trim', explode("\n", $input)));
        foreach ($lines as $line) {
            $parts = preg_split('/[\s,;]+/', $line);
            foreach ($parts as $p) {
                $n = preg_replace('/\D/', '', $p);
                if (strlen($n) >= 10) {
                    if (str_starts_with($n, '0')) {
                        $n = '44' . substr($n, 1);
                    } elseif (!str_starts_with($n, '44')) {
                        $n = '44' . $n;
                    }
                    $numbers[] = '+' . $n;
                }
            }
        }
        return array_unique($numbers);
    }

    public function sendCsv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:2048',
            'message' => 'required|string|max:160',
            'campaign_name' => 'nullable|string|max:255',
        ]);

        $content = file_get_contents($validated['csv']->getRealPath());
        $lines = array_map('str_getcsv', explode("\n", $content));
        $headers = array_map('strtolower', array_map('trim', $lines[0] ?? []));
        $phoneCol = null;
        foreach (['phone', 'mobile', 'number', 'customer_phone', 'tel'] as $col) {
            if (in_array($col, $headers)) {
                $phoneCol = array_search($col, $headers);
                break;
            }
        }
        if ($phoneCol === null) {
            $phoneCol = 0;
        }

        $recipients = [];
        for ($i = 1; $i < count($lines); $i++) {
            $row = $lines[$i] ?? [];
            $val = trim($row[$phoneCol] ?? '');
            $n = preg_replace('/\D/', '', $val);
            if (strlen($n) >= 10) {
                if (str_starts_with($n, '0')) {
                    $n = '44' . substr($n, 1);
                } elseif (!str_starts_with($n, '44')) {
                    $n = '44' . $n;
                }
                $recipients[] = '+' . $n;
            }
        }
        $recipients = array_unique($recipients);

        if (empty($recipients)) {
            return redirect()->route('admin.sms-marketing.index')->with('error', 'No valid phone numbers found in CSV.');
        }

        $twilioSid = config('services.twilio.sid') ?: SiteSetting::get('twilio_sid');
        $twilioToken = config('services.twilio.token') ?: SiteSetting::get('twilio_token');
        $twilioFrom = config('services.twilio.from') ?: SiteSetting::get('twilio_from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            return redirect()->route('admin.sms-marketing.index')
                ->with('error', 'SMS not configured. Add Twilio credentials in Site Settings → APIs.');
        }

        $campaignName = $validated['campaign_name'] ?? 'CSV – ' . now()->format('d M Y H:i');
        $campaign = SmsCampaign::create([
            'name' => $campaignName,
            'message' => $validated['message'],
            'source' => 'csv',
            'total_recipients' => count($recipients),
        ]);

        $sent = 0;
        $failed = 0;
        foreach ($recipients as $to) {
            try {
                $response = Http::asForm()->withBasicAuth($twilioSid, $twilioToken)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages", [
                        'To' => $to,
                        'From' => $twilioFrom,
                        'Body' => $validated['message'],
                    ]);
                if ($response->successful()) {
                    $sent++;
                } else {
                    $failed++;
                    Log::warning('SMS send failed', ['to' => $to, 'status' => $response->status()]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('SMS send error', ['to' => $to, 'error' => $e->getMessage()]);
            }
        }

        $campaign->update(['sent_count' => $sent, 'failed_count' => $failed]);

        return redirect()->route('admin.sms-marketing.index')
            ->with('success', "Sent {$sent} of " . count($recipients) . " message(s) from CSV.");
    }
}
