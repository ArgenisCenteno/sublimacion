<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:orders.view')->only(['index', 'adminShow']);
        $this->middleware('can:orders.manage')->only(['updateStatus']);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items', 'payments' => fn ($q) => $q->with('receipt')->orderBy('created_at', 'desc')]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'amountPaid' => $order->amountPaid(),
            'balanceDue' => $order->balanceDue(),
            'paypalClientId' => config('paypal.sandbox.client_id'),
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Order::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('canal')) {
            $query->where('canal', $request->string('canal'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'canal' => $request->string('canal')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function adminShow(Order $order): Response
    {
        $order->load([
            'items',
            'payments' => fn ($q) => $q->with(['receipt', 'bankDeposit'])->orderBy('created_at', 'desc'),
            'statusHistory.changedBy',
        ]);

        return Inertia::render('Orders/AdminShow', [
            'order' => $order,
            'amountPaid' => $order->amountPaid(),
            'balanceDue' => $order->balanceDue(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        abort_if(in_array($order->status, ['entregado', 'cancelado'], true), 422, 'Este pedido ya está en un estado final.');

        $validated = $request->validate([
            'status' => 'required|in:en_preparacion,enviado,entregado,cancelado',
            'tracking_number' => [
                'nullable', 'string', 'max:255',
                Rule::requiredIf(fn () => $request->input('status') === 'enviado' && $order->delivery_type === 'envio_nacional'),
            ],
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $previousStatus = $order->status;

            if ($validated['status'] === 'cancelado') {
                $this->restoreStock($order);
            }

            $order->update([
                'status' => $validated['status'],
                'tracking_number' => $validated['tracking_number'] ?? $order->tracking_number,
            ]);

            $order->statusHistory()->create([
                'status' => $validated['status'],
                'changed_by' => $request->user()->id,
                'note' => $validated['note'] ?? null,
            ]);

            activity()
                ->performedOn($order)
                ->causedBy($request->user())
                ->useLog('pedido')
                ->withProperties(['from' => $previousStatus, 'to' => $validated['status']])
                ->log("Cambió estado de pedido #{$order->id} de {$previousStatus} a {$validated['status']}");

            $order->user?->notify(new OrderStatusChanged($order));
        });

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    private function restoreStock(Order $order): void
    {
        $order->load(['items.product', 'items.variant']);

        foreach ($order->items as $item) {
            /** @var OrderItem $item */
            if ($item->variant && $item->variant->stock !== null) {
                $item->variant->increment('stock', $item->quantity);
            } elseif ($item->product && $item->product->stock !== null) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }
}
