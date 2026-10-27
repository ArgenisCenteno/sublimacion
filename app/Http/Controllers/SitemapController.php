<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $staticUrls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => url('/catalogo'), 'priority' => '0.9'],
            ['loc' => url('/privacidad'), 'priority' => '0.3'],
            ['loc' => url('/cookies'), 'priority' => '0.3'],
            ['loc' => url('/terminos'), 'priority' => '0.3'],
        ];

        $productUrls = Product::where('active', true)
            ->get(['slug', 'updated_at'])
            ->map(fn (Product $product) => [
                'loc' => url("/catalogo/{$product->slug}"),
                'priority' => '0.7',
                'lastmod' => $product->updated_at?->toAtomString(),
            ])
            ->all();

        $urls = [...$staticUrls, ...$productUrls];

        return response()
            ->view('sitemap.index', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
