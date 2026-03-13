<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Faq;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;

class ExportController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $categories = ServiceCategory::orderBy('sort_order')->get();
        $catMap = [];
        foreach ($categories as $c) {
            $catMap[$c->id] = [
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
            ];
        }

        $categoriesOut = [];
        foreach ($categories as $c) {
            $categoriesOut[$c->slug] = [
                'label' => $c->label,
                'sortOrder' => $c->sort_order,
            ];
        }

        $json = json_encode([
            'services' => $servicesOut,
            'categories' => $categoriesOut,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = base_path('../data/services.json');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (file_put_contents($path, $json) === false) {
            return redirect()->route('admin.dashboard')->with('error', 'Could not write services.json. Check permissions.');
        }

        return redirect()->route('admin.dashboard')->with('success', 'Exported to data/services.json successfully.');
    }
}
