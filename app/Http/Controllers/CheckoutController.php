<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): RedirectResponse|Response
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load(['items.product', 'items.variant']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $requiresDelivery = $cart->items->contains(fn ($item) => $item->product->type === 'producto');

        $items = $cart->items->map(fn ($item) => [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice(),
            'subtotal' => $item->subtotal(),
            'product_name' => $item->product->name,
            'variant_name' => $item->variant?->name,
        ]);

        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
            'requiresDelivery' => $requiresDelivery,
            'customer' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'cedula_rif' => $request->user()->cedula_rif,
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user) {
            $cart = Cart::where('user_id', $user->id)->firstOrFail();
            $cart->load(['items.product', 'items.variant']);

            abort_if($cart->items->isEmpty(), 422, 'Tu carrito está vacío.');

            foreach ($cart->items as $item) {
                $available = $item->availableStock();
                abort_if($available !== null && $item->quantity > $available, 422, "Stock insuficiente para {$item->product->name}.");
            }

            $subtotal = $cart->items->sum(fn ($item) => $item->subtotal());

            $order = Order::create([
                'user_id' => $user->id,
                'canal' => 'online',
                'status' => 'pendiente_pago',
                'delivery_type' => $validated['delivery_type'] ?? null,
                'shipping_carrier' => $validated['shipping_carrier'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_city' => $validated['shipping_city'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'customer_cedula_rif' => $validated['customer_cedula_rif'] ?? null,
                'payment_method' => $validated['payment_method'],
                'subtotal_usd' => $subtotal,
                'shipping_cost_usd' => 0,
                'total_usd' => $subtotal,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'unit_price_usd' => $item->unitPrice(),
                    'quantity' => $item->quantity,
                    'subtotal_usd' => $item->subtotal(),
                ]);

                if ($item->variant) {
                    if ($item->variant->stock !== null) {
                        $item->variant->decrement('stock', $item->quantity);
                    }
                } elseif ($item->product->stock !== null) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            $order->statusHistory()->create([
                'status' => 'pendiente_pago',
                'changed_by' => $user->id,
                'note' => 'Pedido creado.',
            ]);

            $cart->items()->delete();

            return $order;
        });

        Notification::send(User::role(['Admin', 'Empleado'])->get(), new NewOrderPlaced($order));

        return redirect()->route('orders.show', $order)->with('success', 'Pedido creado correctamente.');
    }
}
