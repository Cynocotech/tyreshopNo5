<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Console\Command;

class ImportServicesCommand extends Command
{
    protected $signature = 'no5:import-services 
                            {file? : Path to JSON file (default: data/services.json)}
                            {--force : Overwrite existing services}';

    protected $description = 'Import services and categories from JSON (from local no5:export-services)';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! $path) {
            $path = base_path('../data/services.json');
        } elseif (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        if (! is_readable($path)) {
            $this->error('File not found or not readable: ' . $path);
            return 1;
        }

        $data = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return 1;
        }

        $categories = $data['categories'] ?? [];
        $services = $data['services'] ?? [];

        // Import categories first
        foreach ($categories as $slug => $cat) {
            $catModel = ServiceCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'label' => $cat['label'] ?? $slug,
                    'sort_order' => $cat['sortOrder'] ?? 0,
                ]
            );
            $this->line('  Category: ' . $catModel->label);
        }

        // Build category slug -> id map
        $catMap = ServiceCategory::pluck('id', 'slug')->toArray();

        $imported = 0;
        foreach ($services as $i => $svc) {
            $slug = $svc['id'] ?? $svc['slug'] ?? ('service-' . $i);
            $catSlug = $svc['category'] ?? '';
            $catId = $catMap[$catSlug] ?? ServiceCategory::first()?->id;

            if (! $catId) {
                $this->warn("  Skipping {$slug}: no category");
                continue;
            }

            $payload = [
                'value' => $svc['value'] ?? $svc['title'] ?? $slug,
                'title' => $svc['title'] ?? $svc['value'] ?? $slug,
                'icon' => $svc['icon'] ?? null,
                'price' => (float) ($svc['price'] ?? 0),
                'hero_mot_price' => isset($svc['heroMOTPrice']) ? (float) $svc['heroMOTPrice'] : null,
                'price_label' => $svc['priceLabel'] ?? $svc['price_display'] ?? '£0',
                'price_display' => $svc['priceDisplay'] ?? $svc['price_label'] ?? '£0',
                'service_category_id' => $catId,
                'is_quote' => (bool) ($svc['isQuote'] ?? false),
                'keywords' => $svc['keywords'] ?? [],
                'sort_order' => $svc['sortOrder'] ?? $i,
                'combo_badge' => $svc['comboBadge'] ?? null,
                'combo_subtitle' => $svc['comboSubtitle'] ?? null,
                'combo_features' => $svc['comboFeatures'] ?? null,
                'combo_saving' => $svc['comboSaving'] ?? null,
                'combo_display_price' => $svc['comboDisplayPrice'] ?? null,
                'is_combo_hot' => (bool) ($svc['isComboHot'] ?? false),
            ];

            Service::updateOrCreate(
                ['slug' => $slug],
                $payload
            );
            $imported++;
            $this->line('  Service: ' . $payload['title']);
        }

        $this->info("Imported {$imported} services and " . count($categories) . ' categories.');
        return 0;
    }
}
