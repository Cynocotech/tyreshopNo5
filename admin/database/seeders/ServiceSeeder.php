<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('../data/services.json');
        $sortOrder = 0;
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            $services = $data['services'] ?? [];
            foreach ($services as $svc) {
                $cat = ServiceCategory::where('slug', $svc['category'] ?? '')->first();
                if (!$cat) continue;
                Service::updateOrCreate(
                    ['slug' => $svc['id'] ?? 'service-' . $sortOrder],
                    [
                        'value' => $svc['value'] ?? '',
                        'title' => $svc['title'] ?? $svc['value'] ?? '',
                        'icon' => $svc['icon'] ?? null,
                        'price' => $svc['price'] ?? 0,
                        'hero_mot_price' => $svc['heroMOTPrice'] ?? null,
                        'price_label' => $svc['priceLabel'] ?? '',
                        'price_display' => $svc['priceDisplay'] ?? '',
                        'service_category_id' => $cat->id,
                        'is_quote' => $svc['isQuote'] ?? false,
                        'keywords' => $svc['keywords'] ?? [],
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
            return;
        }
        // Default fallback
        $cat = ServiceCategory::first();
        if ($cat) {
            Service::updateOrCreate(
                ['slug' => 'mot-test'],
                [
                    'value' => 'MOT Test',
                    'title' => 'MOT Test Only',
                    'icon' => '🛞',
                    'price' => 54.85,
                    'price_label' => '£54.85',
                    'price_display' => '£54.85',
                    'service_category_id' => $cat->id,
                    'is_quote' => false,
                    'keywords' => [],
                    'sort_order' => 0,
                ]
            );
        }
    }
}
