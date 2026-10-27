<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function home(): Response
    {
        $products = Product::query()
            ->where('active', true)
            ->with(['category:id,name', 'images', 'variants' => fn ($q) => $q->where('active', true)])
            ->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(60)
            ->get();

        $categories = Category::query()
            ->withCount(['products' => fn ($q) => $q->where('active', true)])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Landing', [
            'featuredProducts' => $products->take(8)->values(),
            'categories' => $categories,
            // Índice ligero para las sugerencias instantáneas del buscador de la portada
            'searchIndex' => $products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'type' => $p->type,
                'price_usd' => $p->price_usd,
                'category' => $p->category?->name,
                'image' => $p->images->first()?->path,
            ])->values(),
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Product::query()
            ->where('active', true)
            ->with(['category', 'images', 'variants' => fn ($q) => $q->where('active', true)]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('price_min')) {
            $query->where('price_usd', '>=', $request->float('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price_usd', '<=', $request->float('price_max'));
        }

        $products = $query->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        return Inertia::render('Catalogo/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'category_id' => $request->string('category_id')->toString(),
                'price_min' => $request->string('price_min')->toString(),
                'price_max' => $request->string('price_max')->toString(),
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->active, 404);

        $product->load([
            'category',
            'images',
            'variants' => fn ($q) => $q->where('active', true),
        ]);

        return Inertia::render('Catalogo/Show', [
            'product' => $product,
        ]);
    }
}
