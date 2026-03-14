<x-admin-layout>
    <x-slot name="header">Site Settings</x-slot>

    <div class="max-w-3xl" x-data="{ tab: '{{ $activeTab ?? old('_tab', 'general') }}' }">
        <div class="flex gap-2 mb-6 border-b border-slate-200 pb-2 overflow-x-auto">
            <button type="button" @click="tab = 'general'" :class="tab === 'general' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">General</button>
            <button type="button" @click="tab = 'address'" :class="tab === 'address' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">Address</button>
            <button type="button" @click="tab = 'contact'" :class="tab === 'contact' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">Contact</button>
            <button type="button" @click="tab = 'hours'" :class="tab === 'hours' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">Opening Hours</button>
            <button type="button" @click="tab = 'hero'" :class="tab === 'hero' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">Hero & Footer</button>
            <button type="button" @click="tab = 'payment'" :class="tab === 'payment' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">Payment Gateways</button>
            <button type="button" @click="tab = 'apis'" :class="tab === 'apis' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-4 py-2 rounded-lg text-sm font-medium shrink-0">APIs</button>
        </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_tab" :value="tab">

        <div x-show="tab === 'general'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-3">General</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Site Description</label>
                    <input type="text" name="site_description" value="{{ old('site_description', $settings['site_description'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">URL</label>
                    <input type="url" name="url" value="{{ old('url', $settings['url'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
            </div>
        </div>

        <div x-show="tab === 'address'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-3">Address</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Street</label>
                    <input type="text" name="address_street" value="{{ old('address_street', $settings['address_street'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Locality</label>
                        <input type="text" name="address_locality" value="{{ old('address_locality', $settings['address_locality'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Region</label>
                        <input type="text" name="address_region" value="{{ old('address_region', $settings['address_region'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Postcode</label>
                        <input type="text" name="address_postcode" value="{{ old('address_postcode', $settings['address_postcode'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Country</label>
                        <input type="text" name="address_country" value="{{ old('address_country', $settings['address_country'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'contact'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-3">Contact</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Phone (display)</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Phone (international)</label>
                    <input type="text" name="phone_international" value="{{ old('phone_international', $settings['phone_international'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="+447895859505">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Logo URL</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url', $settings['logo_url'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
            </div>
        </div>

        <div x-show="tab === 'hours'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-3">Opening Hours</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Days (comma-separated)</label>
                    <input type="text" name="opening_days" value="{{ old('opening_days', $settings['opening_days'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Monday,Tuesday,...">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Opens</label>
                        <input type="text" name="opening_time" value="{{ old('opening_time', $settings['opening_time'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="08:00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Closes</label>
                        <input type="text" name="closing_time" value="{{ old('closing_time', $settings['closing_time'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="18:00">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Display text (shown on site)</label>
                    <input type="text" name="opening_hours_display" value="{{ old('opening_hours_display', $settings['opening_hours_display'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Mon–Sat: 8am–6pm">
                </div>
            </div>
        </div>

        <div x-show="tab === 'hero'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-3">Hero & Footer</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Palmers Green · North London">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Footer Tagline</label>
                    <input type="text" name="footer_tagline" value="{{ old('footer_tagline', $settings['footer_tagline'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Footer Description</label>
                    <textarea name="footer_description" rows="2" class="w-full mt-1 rounded border-slate-300">{{ old('footer_description', $settings['footer_description'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Copyright</label>
                    <input type="text" name="copyright" value="{{ old('copyright', $settings['copyright'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Hero Book Price</label>
                        <input type="text" name="hero_book_price" value="{{ old('hero_book_price', $settings['hero_book_price'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="£19">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Hero Save</label>
                        <input type="text" name="hero_save" value="{{ old('hero_save', $settings['hero_save'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="£31">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Footer MOT Price</label>
                        <input type="text" name="footer_mot_price" value="{{ old('footer_mot_price', $settings['footer_mot_price'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="£19">
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <h4 class="font-semibold text-slate-700 mb-3">Footer “Today’s Offer” Box</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Offer Title</label>
                            <input type="text" name="footer_offer_title" value="{{ old('footer_offer_title', $settings['footer_offer_title'] ?? "Today's Offer") }}" class="w-full mt-1 rounded border-slate-300" placeholder="Today's Offer">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Subtitle</label>
                            <input type="text" name="footer_offer_subtitle" value="{{ old('footer_offer_subtitle', $settings['footer_offer_subtitle'] ?? 'Book Today') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Book Today">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Offer Label</label>
                            <input type="text" name="footer_offer_label" value="{{ old('footer_offer_label', $settings['footer_offer_label'] ?? 'MOT + Service') }}" class="w-full mt-1 rounded border-slate-300" placeholder="MOT + Service">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Was Price (strikethrough)</label>
                            <input type="text" name="footer_offer_was_price" value="{{ old('footer_offer_was_price', $settings['footer_offer_was_price'] ?? '£50') }}" class="w-full mt-1 rounded border-slate-300" placeholder="£50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Save Text</label>
                            <input type="text" name="footer_offer_save" value="{{ old('footer_offer_save', $settings['footer_offer_save'] ?? 'Save £31+') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Save £31+">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Feature Line</label>
                            <input type="text" name="footer_offer_feature" value="{{ old('footer_offer_feature', $settings['footer_offer_feature'] ?? '🚗 Free collection & delivery') }}" class="w-full mt-1 rounded border-slate-300" placeholder="🚗 Free collection & delivery">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Button Text</label>
                            <input type="text" name="footer_offer_btn" value="{{ old('footer_offer_btn', $settings['footer_offer_btn'] ?? 'Book Now →') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Book Now →">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Disclaimer</label>
                            <input type="text" name="footer_offer_disclaimer" value="{{ old('footer_offer_disclaimer', $settings['footer_offer_disclaimer'] ?? '*New bookings only. Excludes commercial vehicles.') }}" class="w-full mt-1 rounded border-slate-300" placeholder="*New bookings only. Excludes commercial vehicles.">
                        </div>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <h4 class="font-medium text-slate-700 mb-3">Combo Deals Section</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Section Title</label>
                            <input type="text" name="combo_section_title" value="{{ old('combo_section_title', $settings['combo_section_title'] ?? 'Special Offer') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Special Offer">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Intro Text</label>
                            <input type="text" name="combo_section_intro" value="{{ old('combo_section_intro', $settings['combo_section_intro'] ?? "Book your MOT together with a service and pay just £19 — saving at least £31.") }}" class="w-full mt-1 rounded border-slate-300" placeholder="Book your MOT together with a service and pay just £19 — saving at least £31.">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Combined Description (under price)</label>
                            <input type="text" name="combo_combined_desc" value="{{ old('combo_combined_desc', $settings['combo_combined_desc'] ?? 'MOT Test + Service combined') }}" class="w-full mt-1 rounded border-slate-300" placeholder="MOT Test + Service combined">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Areas Intro Text</label>
                    <input type="text" name="areas_intro" value="{{ old('areas_intro', $settings['areas_intro'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                </div>
                <div class="pt-3 border-t border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_update_notice" value="1" {{ old('show_update_notice', $settings['show_update_notice'] ?? '1') === '1' ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-700">Show “Site under update” notice</span>
                    </label>
                    <p class="text-xs text-slate-500 mt-1 ml-6">When enabled, an amber banner appears at the top of the site asking visitors to please be patient during updates.</p>
                </div>
            </div>
        </div>

        <div x-show="tab === 'payment'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-4">Payment Gateways</h3>
            <p class="text-sm text-slate-600 mb-4">Configure card terminals and payment APIs for the EPOS. When Card is selected at checkout, the configured terminal will be used.</p>

            {{-- Card terminal provider (Stripe Terminal or Teya SPI) --}}
            <div class="mb-6 pb-4 border-b border-slate-200">
                <label class="block text-sm font-medium text-slate-700 mb-2">Card terminal provider</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="payment_card_provider" value="stripe_terminal" {{ old('payment_card_provider', $settings['payment_card_provider'] ?? '') === 'stripe_terminal' ? 'checked' : '' }}>
                        <span>Stripe Terminal</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="payment_card_provider" value="teya_spi" {{ old('payment_card_provider', $settings['payment_card_provider'] ?? '') === 'teya_spi' ? 'checked' : '' }}>
                        <span>Teya SPI</span>
                    </label>
                </div>
            </div>

            {{-- Stripe Terminal --}}
            <div class="mb-6 pb-4 border-b border-slate-200">
                <h4 class="font-medium text-slate-700 mb-2">Stripe Terminal</h4>
                <p class="text-xs text-slate-500 mb-3">In-person card payments via Stripe readers. Uses PaymentIntent + reader hand-off.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Secret key</label>
                        <input type="password" name="payment_stripe_secret" value="{{ old('payment_stripe_secret', $settings['payment_stripe_secret'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="sk_test_... or sk_live_..." autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Publishable key</label>
                        <input type="text" name="payment_stripe_publishable" value="{{ old('payment_stripe_publishable', $settings['payment_stripe_publishable'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="pk_test_... or pk_live_...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Reader ID (optional)</label>
                        <input type="text" name="payment_stripe_reader_id" value="{{ old('payment_stripe_reader_id', $settings['payment_stripe_reader_id'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="tmr_xxx – defaults to first online reader if blank">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="payment_stripe_enabled" value="0">
                        <input type="checkbox" name="payment_stripe_enabled" value="1" {{ old('payment_stripe_enabled', $settings['payment_stripe_enabled'] ?? '') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Enable Stripe Terminal for EPOS</span>
                    </label>
                </div>
            </div>

            {{-- Teya SPI --}}
            <div class="mb-6 pb-4 border-b border-slate-200">
                <h4 class="font-medium text-slate-700 mb-2">Teya SPI / Poslink</h4>
                <p class="text-xs text-slate-500 mb-3">Connect to Teya card machines via Poslink API.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">API URL</label>
                        <input type="url" name="payment_teya_api_url" value="{{ old('payment_teya_api_url', $settings['payment_teya_api_url'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="https://api.teya.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Merchant ID</label>
                        <input type="text" name="payment_teya_merchant_id" value="{{ old('payment_teya_merchant_id', $settings['payment_teya_merchant_id'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">API key / Secret</label>
                        <input type="password" name="payment_teya_api_key" value="{{ old('payment_teya_api_key', $settings['payment_teya_api_key'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" autocomplete="off">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="payment_teya_enabled" value="0">
                        <input type="checkbox" name="payment_teya_enabled" value="1" {{ old('payment_teya_enabled', $settings['payment_teya_enabled'] ?? '') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Enable Teya SPI for EPOS</span>
                    </label>
                </div>
            </div>

            {{-- Faster Payments --}}
            <div class="mb-6 pb-4 border-b border-slate-200">
                <h4 class="font-medium text-slate-700 mb-2">Faster Payments API</h4>
                <p class="text-xs text-slate-500 mb-3">UK real-time bank transfers (via provider e.g. Open Banking, ClearBank).</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">API URL</label>
                        <input type="url" name="payment_faster_api_url" value="{{ old('payment_faster_api_url', $settings['payment_faster_api_url'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Client ID</label>
                        <input type="text" name="payment_faster_client_id" value="{{ old('payment_faster_client_id', $settings['payment_faster_client_id'] ?? '') }}" class="w-full mt-1 rounded border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Client secret</label>
                        <input type="password" name="payment_faster_client_secret" value="{{ old('payment_faster_client_secret', $settings['payment_faster_client_secret'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" autocomplete="off">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="payment_faster_enabled" value="0">
                        <input type="checkbox" name="payment_faster_enabled" value="1" {{ old('payment_faster_enabled', $settings['payment_faster_enabled'] ?? '') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Enable Faster Payments</span>
                    </label>
                </div>
            </div>

            {{-- LFAT Pay --}}
            <div class="mb-6">
                <h4 class="font-medium text-slate-700 mb-2">LFAT Pay</h4>
                <p class="text-xs text-slate-500 mb-3">Additional payment gateway. Configure API credentials as provided by your provider.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">API URL</label>
                        <input type="url" name="payment_lfat_api_url" value="{{ old('payment_lfat_api_url', $settings['payment_lfat_api_url'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">API key</label>
                        <input type="password" name="payment_lfat_api_key" value="{{ old('payment_lfat_api_key', $settings['payment_lfat_api_key'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" autocomplete="off">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="payment_lfat_enabled" value="0">
                        <input type="checkbox" name="payment_lfat_enabled" value="1" {{ old('payment_lfat_enabled', $settings['payment_lfat_enabled'] ?? '') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Enable LFAT Pay</span>
                    </label>
                </div>
            </div>
        </div>

        <div x-show="tab === 'apis'" x-cloak>
            <h3 class="font-semibold text-slate-800 mb-4">External APIs</h3>
            <p class="text-sm text-slate-600 mb-4">API keys for vehicle lookup, integrations, and third-party services.</p>

            <div class="mb-6 pb-4 border-b border-slate-200">
                <h4 class="font-medium text-slate-700 mb-2">Vehicle Registration (VRN) Lookup</h4>
                <p class="text-xs text-slate-500 mb-3">Used for plate lookup on the front page and booking flow. Sign up at <a href="https://api.checkcardetails.co.uk/auth/register" target="_blank" rel="noopener" class="text-blue-600 hover:underline">api.checkcardetails.co.uk</a> for an API key.</p>
                <div>
                    <label class="block text-sm font-medium text-slate-700">API Key</label>
                    <input type="password" name="vrn_api_key" value="{{ old('vrn_api_key', $settings['vrn_api_key'] ?? '') }}" class="w-full mt-1 rounded border-slate-300" placeholder="Your Check Car Details API key" autocomplete="off">
                    <p class="text-xs text-slate-500 mt-1">Leave blank to use <code class="bg-slate-100 px-1 rounded">CHECK_CAR_DETAILS_API_KEY</code> from .env (if set).</p>
                </div>
            </div>
        </div>

        <style>[x-cloak]{display:none!important}</style>

        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
        </div>
    </form>
    </div>
</x-admin-layout>
