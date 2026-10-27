<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use App\Rules\PersonName;
use App\Rules\PhoneNumber;
use App\Rules\VenezuelanCedulaRif;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:pos.vender');
    }

    public function index(Request $request): Response
    {
        $shift = $this->currentShift($request);

        $products = Product::with(['category', 'variants' => fn ($q) => $q->where('active', true)])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Pos/Index', [
            'currentShift' => $shift,
            'shiftTotals' => $shift ? $shift->totalsByMethod() : null,
            'products' => $products,
        ]);
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $customers = User::role('Cliente')
            ->where('email', '!=', 'venta-rapida@pos.local')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('cedula_rif', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'cedula_rif']);

        return response()->json($customers);
    }

    public function storeShift(Request $request): RedirectResponse
    {
        abort_if($this->currentShift($request) !== null, 422, 'Ya tienes un turno abierto.');

        $validated = $request->validate([
            'opening_cash_usd' => 'required|numeric|min:0',
            'opening_cash_bs' => 'required|numeric|min:0',
        ]);

        CashShift::create([
            'user_id' => $request->user()->id,
            'opened_at' => now(),
            'opening_cash_usd' => $validated['opening_cash_usd'],
            'opening_cash_bs' => $validated['opening_cash_bs'],
            'status' => 'abierto',
        ]);

        return back()->with('success', 'Turno abierto correctamente.');
    }

    public function closeShift(Request $request, CashShift $shift): RedirectResponse
    {
        abort_unless($shift->user_id === $request->user()->id, 403);
        abort_unless($shift->status === 'abierto', 422, 'Este turno ya está cerrado.');

        $validated = $request->validate([
            'counted_cash_usd' => 'required|numeric|min:0',
            'counted_cash_bs' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $shift->close((float) $validated['counted_cash_usd'], (float) $validated['counted_cash_bs'], $validated['notes'] ?? null);

        return redirect()->route('pos.index')->with('success', 'Turno cerrado correctamente.');
    }

    public function storeSale(Request $request): RedirectResponse
    {
        $shift = $this->currentShift($request);
        abort_unless($shift !== null, 422, 'Debes abrir un turno de caja antes de vender.');

        $validated = $request->validate([
            'customer_user_id' => 'nullable|exists:users,id',
            'customer_name' => ['required', 'string', 'max:255', new PersonName()],
            'customer_phone' => ['required', 'string', 'max:30', new PhoneNumber()],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_cedula_rif' => ['nullable', 'string', new VenezuelanCedulaRif()],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:pago_movil,transferencia,efectivo,paypal',
            'payments.*.currency' => 'nullable|in:USD,BS',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:255',
        ]);

        $order = DB::transaction(function () use ($validated, $request, $shift) {
            $customerUserId = $validated['customer_user_id']
                ?? User::where('email', 'venta-rapida@pos.local')->value('id');

            $lineItems = [];
            $subtotal = 0;

            foreach ($validated['items'] as $line) {
                $product = Product::findOrFail($line['product_id']);
                $variant = ! empty($line['product_variant_id'])
                    ? $product->variants()->findOrFail($line['product_variant_id'])
                    : null;

                $available = $variant ? $variant->stock : $product->stock;
                abort_if($available !== null && $line['quantity'] > $available, 422, "Stock insuficiente para {$product->name}.");

                $unitPrice = (float) ($variant->price_override ?? $product->price_usd);
                $lineSubtotal = $unitPrice * $line['quantity'];
                $subtotal += $lineSubtotal;

                $lineItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'user_id' => $customerUserId,
                'canal' => 'tienda_fisica',
                'status' => 'entregado',
                'delivery_type' => 'retiro_tienda',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'customer_cedula_rif' => $validated['customer_cedula_rif'] ?? null,
                'payment_method' => $validated['payments'][0]['method'],
                'subtotal_usd' => $subtotal,
                'shipping_cost_usd' => 0,
                'total_usd' => $subtotal,
            ]);

            foreach ($lineItems as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant']?->id,
                    'product_name' => $line['product']->name,
                    'variant_name' => $line['variant']?->name,
                    'unit_price_usd' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'subtotal_usd' => $line['subtotal'],
                ]);

                if ($line['variant'] && $line['variant']->stock !== null) {
                    $line['variant']->decrement('stock', $line['quantity']);
                } elseif ($line['product']->stock !== null) {
                    $line['product']->decrement('stock', $line['quantity']);
                }
            }

            $order->statusHistory()->create([
                'status' => 'entregado',
                'changed_by' => $request->user()->id,
                'note' => 'Venta registrada desde el POS.',
            ]);

            foreach ($validated['payments'] as $line) {
                $amounts = Payment::computeAmounts($line['method'], $line['currency'] ?? null, (float) $line['amount']);

                $payment = $order->payments()->create([
                    'amount_usd' => $amounts['amount_usd'],
                    'amount_bs' => $amounts['amount_bs'],
                    'exchange_rate' => $amounts['exchange_rate'] ?? null,
                    'currency' => $line['currency'] ?? null,
                    'method' => $line['method'],
                    'reference' => $line['reference'] ?? null,
                    'cash_shift_id' => $shift->id,
                    'status' => 'pendiente',
                    'submitted_by' => $request->user()->id,
                    'paid_at' => now(),
                ]);

                $payment->markVerified($request->user()->id);
            }

            return $order;
        });

        Notification::send(User::role(['Admin', 'Empleado'])->get(), new NewOrderPlaced($order));

        return redirect()->route('admin.orders.show', $order)->with('success', 'Venta registrada correctamente.');
    }

    private function currentShift(Request $request): ?CashShift
    {
        return CashShift::where('user_id', $request->user()->id)
            ->where('status', 'abierto')
            ->first();
    }
}
