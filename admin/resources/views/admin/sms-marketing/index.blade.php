<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span>SMS Marketing</span>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        @if($campaigns->isNotEmpty())
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h3 class="text-base font-semibold text-slate-800 px-6 py-4 border-b border-slate-200">Campaigns</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="text-left px-6 py-3 font-medium text-slate-700">Campaign</th>
                            <th class="text-right px-6 py-3 font-medium text-slate-700">Total</th>
                            <th class="text-right px-6 py-3 font-medium text-slate-700">Sent</th>
                            <th class="text-right px-6 py-3 font-medium text-slate-700">Failed</th>
                            <th class="text-left px-6 py-3 font-medium text-slate-700">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $c)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-6 py-3">
                                <span class="font-medium text-slate-800">{{ $c->name }}</span>
                                <span class="text-slate-500 text-xs ml-1">({{ $c->source }})</span>
                            </td>
                            <td class="text-right px-6 py-3 text-slate-600">{{ $c->total_recipients }}</td>
                            <td class="text-right px-6 py-3 text-emerald-600 font-medium">{{ $c->sent_count }}</td>
                            <td class="text-right px-6 py-3 {{ $c->failed_count ? 'text-amber-600' : 'text-slate-400' }}">{{ $c->failed_count }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $c->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-2">Send SMS to customers</h3>
            <p class="text-sm text-slate-600 mb-4">Enter phone numbers (one per line or comma-separated), or paste from a CSV. UK numbers: 07xxx or +447xxx. Configure Twilio in Settings → APIs.</p>
            <form action="{{ route('admin.sms-marketing.send') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="campaign_name" class="block text-sm font-medium text-slate-700 mb-1">Campaign name (optional)</label>
                    <input type="text" name="campaign_name" id="campaign_name" maxlength="255"
                        class="w-full rounded-lg border-slate-300"
                        placeholder="e.g. Spring promo 2025">
                </div>
                <div class="mb-4">
                    <label for="recipients" class="block text-sm font-medium text-slate-700 mb-1">Phone numbers</label>
                    <textarea name="recipients" id="recipients" rows="6" required
                        class="w-full rounded-lg border-slate-300 font-mono text-sm"
                        placeholder="07895123456&#10;07987654321&#10;+447895123456"></textarea>
                    <p class="text-xs text-slate-500 mt-1">One per line, or comma/space separated</p>
                </div>
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message (max 160 chars)</label>
                    <textarea name="message" id="message" rows="3" maxlength="160" required
                        class="w-full rounded-lg border-slate-300"
                        placeholder="Hi! Thanks for visiting NO5. We hope you're happy with our service. Rate us on Google!"></textarea>
                    <p class="text-xs text-slate-500 mt-1"><span id="char-count">0</span>/160</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">Send SMS</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-2">Upload CSV</h3>
            <p class="text-sm text-slate-600 mb-4">Upload a CSV file with a column containing phone numbers. The first row is headers. We'll detect columns named "phone", "mobile", "number", or "customer_phone".</p>
            <form action="{{ route('admin.sms-marketing.send-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="campaign_name_csv" class="block text-sm font-medium text-slate-700 mb-1">Campaign name (optional)</label>
                    <input type="text" name="campaign_name" id="campaign_name_csv" maxlength="255"
                        class="w-full rounded-lg border-slate-300"
                        placeholder="e.g. Customer list March 2025">
                </div>
                <div class="mb-4">
                    <input type="file" name="csv" accept=".csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
                <div class="mb-4">
                    <label for="message-csv" class="block text-sm font-medium text-slate-700 mb-1">Message (max 160 chars)</label>
                    <textarea name="message" id="message-csv" rows="3" maxlength="160" required
                        class="w-full rounded-lg border-slate-300"
                        placeholder="Promotional message..."></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">Send to CSV numbers</button>
            </form>
        </div>

        <div class="bg-slate-50 rounded-lg p-4">
            <p class="text-sm text-slate-600"><strong>Tip:</strong> You can export customer phone numbers from Attended customers or Bookings, or use your own list. Ensure you have consent to message customers.</p>
        </div>
    </div>

    <script>
    document.getElementById('message')?.addEventListener('input', function() {
        document.getElementById('char-count').textContent = this.value.length;
    });
    </script>
</x-admin-layout>
