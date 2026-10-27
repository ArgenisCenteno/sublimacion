<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['create', 'store', 'storeImage']);
        $this->middleware('can:products.edit')->only(['edit', 'update']);
        $this->middleware('can:products.delete')->only(['destroy', 'destroyImage']);
    }

    public function index(Request $request): Response
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'category_id' => $request->string('category_id')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product = Product::create($validated);

        $this->syncVariants($product, $request->input('variants', []));
        $this->storeUploadedImages($product, $request->file('images', []));

        return redirect()->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'images', 'variants']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['images', 'variants']);

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        $this->syncVariants($product, $request->input('variants', []));

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function storeImage(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $this->storeUploadedImages($product, $request->file('images'));

        return back()->with('success', 'Imagen agregada correctamente.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:producto,servicio',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_usd' => 'required|numeric|min:0',
            'sku' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'stock' => 'nullable|integer|min:0',
            'active' => 'boolean',
            'featured' => 'boolean',
        ]);
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $product->variants()->delete();

        foreach ($variants as $variant) {
            if (empty($variant['name'])) {
                continue;
            }

            $product->variants()->create([
                'name' => $variant['name'],
                'price_override' => $variant['price_override'] ?? null,
                'stock' => $variant['stock'] ?? null,
                'sku' => $variant['sku'] ?? null,
                'active' => $variant['active'] ?? true,
            ]);
        }
    }

    private function storeUploadedImages(Product $product, array $images): void
    {
        $nextOrder = $product->images()->max('sort_order') + 1;

        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'sort_order' => $nextOrder++,
            ]);
        }
    }
}
