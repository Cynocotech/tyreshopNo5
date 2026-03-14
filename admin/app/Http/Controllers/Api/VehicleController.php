<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VehicleController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $vrm = strtoupper(str_replace(' ', '', $request->query('vrm', '')));
        if (! $vrm) {
            return response()->json(['error' => 'VRM (registration) required'], 400);
        }

        $apiKey = SiteSetting::get('vrn_api_key') ?: config('services.checkcar.api_key');
        $mockMode = config('services.checkcar.mock', false);

        if (! $mockMode && ! $apiKey) {
            return response()->json([
                'error' => 'Vehicle API not configured',
                'hint' => 'Add your VRN API key in Admin → Settings → APIs. Sign up at api.checkcardetails.co.uk/auth/register',
            ], 503);
        }

        if ($mockMode) {
            return response()->json($this->getMockResponse($vrm));
        }

        try {
            $base = config('services.checkcar.base', 'https://api.checkcardetails.co.uk');
            $attempts = [
                "{$base}/vehicledata/vehicleregistration?" . http_build_query(['apikey' => $apiKey, 'vrm' => $vrm]),
                "{$base}/api/vehicle?" . http_build_query(['vrm' => $vrm, 'apikey' => $apiKey]),
                "{$base}/api/vehicle?" . http_build_query(['registrationNumber' => $vrm, 'apikey' => $apiKey]),
            ];

            $res = null;
            foreach ($attempts as $url) {
                $res = Http::get($url);
                if ($res->successful()) {
                    break;
                }
            }

            if (! $res || ! $res->successful()) {
                $body = $res ? $res->json() : [];
                $msg = $body['message'] ?? $body['error'] ?? $body['detail'] ?? ($res ? $res->body() : 'Request failed');
                return response()->json([
                    'error' => 'Vehicle lookup failed',
                    'detail' => is_string($msg) ? substr($msg, 0, 200) : 'Registration not found or API error',
                ], $res ? $res->status() : 503);
            }

            $data = $res->json();
            return response()->json($this->normaliseResponse($data ?? [], $vrm));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Vehicle lookup failed', 'detail' => $e->getMessage()], 500);
        }
    }

    private function normaliseResponse(array $raw, string $vrm): array
    {
        if (empty($raw)) {
            return ['error' => 'No data returned'];
        }
        if (isset($raw['VehicleRegistration']) || isset($raw['Vehicle']['VehicleRegistration'])) {
            return $raw;
        }
        // Map flat/alternate API response to expected structure
        $vr = $raw['registrationNumber'] ?? $raw['RegistrationNumber'] ?? $vrm;
        return [
            'VehicleRegistration' => [
                'Vrm' => $raw['registrationNumber'] ?? $vr,
                'Make' => $raw['make'] ?? $raw['Make'] ?? null,
                'Model' => $raw['model'] ?? $raw['Model'] ?? null,
                'MakeModel' => ($raw['make'] ?? '') . ' ' . ($raw['model'] ?? ''),
                'Colour' => $raw['colour'] ?? $raw['Colour'] ?? null,
                'FuelType' => $raw['fuelType'] ?? $raw['FuelType'] ?? null,
                'YearOfManufacture' => $raw['yearOfManufacture'] ?? $raw['YearOfManufacture'] ?? null,
                'Transmission' => $raw['transmission'] ?? $raw['Transmission'] ?? null,
                ...$raw,
            ],
        ];
    }

    private function getMockResponse(string $vrm): array
    {
        return [
            'VehicleRegistration' => [
                'Make' => 'MINI',
                'Model' => 'COOPER S AUTO',
                'MakeModel' => 'MINI COOPER S AUTO',
                'Vrm' => $vrm,
                'Colour' => 'BLACK',
                'YearOfManufacture' => '2007',
                'FuelType' => 'PETROL',
                'Transmission' => 'AUTO 6 GEARS',
            ],
        ];
    }
}
