<?php

namespace App\Services;

use App\Models\SiteSetting;
use Stripe\StripeClient;

class PaymentGatewayService
{
    public function cardTerminalConfig(): array
    {
        $provider = SiteSetting::get('payment_card_provider', '');
        $stripeEnabled = SiteSetting::get('payment_stripe_enabled') === '1';
        $teyaEnabled = SiteSetting::get('payment_teya_enabled') === '1';

        $stripeReady = $stripeEnabled && (SiteSetting::get('payment_stripe_secret') ?: config('services.stripe.secret'));
        $teyaReady = $teyaEnabled && SiteSetting::get('payment_teya_api_url') && SiteSetting::get('payment_teya_api_key');

        return [
            'provider' => $provider,
            'stripe_terminal' => [
                'enabled' => $stripeEnabled,
                'ready' => $stripeReady,
            ],
            'teya_spi' => [
                'enabled' => $teyaEnabled,
                'ready' => $teyaReady,
            ],
            'card_terminal_available' => ($provider === 'stripe_terminal' && $stripeReady) || ($provider === 'teya_spi' && $teyaReady),
        ];
    }

    public function createStripeTerminalPayment(float $amountPence): array
    {
        $secret = SiteSetting::get('payment_stripe_secret') ?: config('services.stripe.secret');
        if (!$secret) {
            return ['success' => false, 'message' => 'Stripe not configured.'];
        }

        try {
            $stripe = new StripeClient($secret);
            $readerId = SiteSetting::get('payment_stripe_reader_id');

            $intent = $stripe->paymentIntents->create([
                'amount' => (int) round($amountPence),
                'currency' => 'gbp',
                'payment_method_types' => ['card_present'],
            ]);

            $readerIdToUse = $readerId;
            if (!$readerIdToUse) {
                $readers = $stripe->terminal->readers->all(['status' => 'online', 'limit' => 1]);
                if (empty($readers->data)) {
                    return ['success' => false, 'message' => 'No Stripe readers online. Connect a reader or specify Reader ID in settings.'];
                }
                $readerIdToUse = $readers->data[0]->id;
            }

            $stripe->terminal->readers->processPaymentIntent($readerIdToUse, [
                'payment_intent' => $intent->id,
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $intent->id,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getStripePaymentStatus(string $paymentIntentId): array
    {
        $secret = SiteSetting::get('payment_stripe_secret') ?: config('services.stripe.secret');
        if (!$secret) {
            return ['success' => false, 'message' => 'Stripe not configured.'];
        }

        try {
            $stripe = new StripeClient($secret);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);
            return [
                'success' => true,
                'status' => $intent->status,
                'succeeded' => $intent->status === 'succeeded',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
