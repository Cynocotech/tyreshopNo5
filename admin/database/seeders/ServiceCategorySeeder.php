<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('../data/services.json');
        if (!file_exists($path)) {
            $this->seedDefaults();
            return;
        }
        $data = json_decode(file_get_contents($path), true);
        $categories = $data['categories'] ?? [];
        foreach ($categories as $slug => $cat) {
            ServiceCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'label' => $cat['label'] ?? $slug,
                    'sort_order' => $cat['sortOrder'] ?? 0,
                ]
            );
        }
    }

    private function seedDefaults(): void
    {
        $defaults = [
            ['slug' => 'special-offers', 'label' => '🔥 Special Offers', 'sort_order' => 1],
            ['slug' => 'servicing-mot', 'label' => 'Servicing & MOT', 'sort_order' => 2],
            ['slug' => 'tyres-other', 'label' => 'Tyres & Other', 'sort_order' => 3],
        ];
        foreach ($defaults as $d) {
            ServiceCategory::updateOrCreate(['slug' => $d['slug']], $d);
        }
    }
}
