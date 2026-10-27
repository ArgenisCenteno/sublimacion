<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $cart = $this->cartFor($request);
        $cart->load(['items.product.images', 'items.variant']);

        $items = $cart->items->map(fn (CartItem $item) => [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice(),
            'subtotal' => $item->subtotal(),
            'available_stock' => $item->availableStock(),
            'product' => [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'type' => $item->product->type,
                'image' => $item->product->images->first()?->path,
            ],
            'variant' => $item->variant ? [
                'id' => $item->variant->id,
                'name' => $item->variant->name,
            ] : null,
        ]);

        return Inertia::render('Cart/Index', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = $validated['product_variant_id'] ?? null
            ? $product->variants()->findOrFail($validated['product_variant_id'])
            : null;

        $cart = $this->cartFor($request);

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->when($variant, fn ($q) => $q->where('product_variant_id', $variant->id))
            ->when(! $variant, fn ($q) => $q->whereNull('product_variant_id'))
            ->first();

        $newQuantity = ($existing?->quantity ?? 0) + $validated['quantity'];

        $availableStock = $variant ? $variant->stock : $product->stock;
        if ($availableStock !== null && $newQuantity > $availableStock) {
            return back()->with('error', 'No hay suficiente stock disponible para esa cantidad.');
        }

        if ($existing) {
            $existing->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $availableStock = $cartItem->availableStock();
        if ($availableStock !== null && $validated['quantity'] > $availableStock) {
            return back()->with('error', 'No hay suficiente stock disponible para esa cantidad.');
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);

        $cartItem->delete();

        return back()->with('success', 'Producto quitado del carrito.');
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }
}
