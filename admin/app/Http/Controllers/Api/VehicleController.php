<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $apiKey = config('services.checkcar.api_key');
        $mockMode = config('services.checkcar.mock', false);

        if (! $mockMode && ! $apiKey) {
            return response()->json([
                'error' => 'Vehicle API not configured',
                'hint' => 'Set CHECK_CAR_DETAILS_API_KEY in .env. Sign up at api.checkcardetails.co.uk/auth/register',
            ], 503);
        }

        if ($mockMode) {
            return response()->json($this->getMockResponse($vrm));
        }

        try {
            $base = config('services.checkcar.base', 'https://api.checkcardetails.co.uk');
            $res = Http::withHeaders(['x-api-key' => $apiKey])
                ->get("{$base}/ukvd/vehicle-registration", ['vrm' => $vrm]);

            if (! $res->successful()) {
                return response()->json(['error' => $res->json('message') ?? 'Vehicle lookup failed'], $res->status());
            }

            return response()->json($res->json());
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
