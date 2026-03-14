<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Console\Command;

class ExportServicesCommand extends Command
{
    protected $signature = 'no5:export-services';

    protected $description = 'Export services and categories to data/services.json for the main site';

    public function handle(): int
    {
        $categories = ServiceCategory::orderBy('sort_order')->get();
        $categoriesOut = [];
        foreach ($categories as $c) {
            $categoriesOut[$c->slug] = [
                'label' => $c->label,
                'sortOrder' => $c->sort_order,
            ];
        }

        $services = Service::with('category')->orderBy('sort_order')->orderBy('title')->get();
        $servicesOut = [];
        foreach ($services as $s) {
            $servicesOut[] = [
                'id' => $s->slug,
                'value' => $s->value,
                'title' => $s->title,
                'icon' => $s->icon,
                'price' => (float) $s->price,
                'heroMOTPrice' => $s->hero_mot_price ? (float) $s->hero_mot_price : null,
                'priceLabel' => $s->price_label,
                'priceDisplay' => $s->price_display,
                'category' => $s->category?->slug ?? '',
                'isQuote' => $s->is_quote,
                'keywords' => $s->keywords ?? [],
                'sortOrder' => $s->sort_order,
            ];
        }

        $json = json_encode([
            'services' => $servicesOut,
            'categories' => $categoriesOut,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = base_path('../data/services.json');
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (file_put_contents($path, $json) === false) {
            $this->error('Could not write to ' . $path);
            return 1;
        }

        $this->info('Exported to data/services.json');
        return 0;
    }
}
