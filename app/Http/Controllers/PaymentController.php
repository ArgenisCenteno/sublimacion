<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentPendingVerification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:payments.view')->only(['index']);
        $this->middleware('can:payments.verify')->only(['verify', 'reject']);
    }

    public function index(): Response
    {
        $payments = Payment::with(['order.user'])
            ->where('status', 'pendiente')
            ->orderBy('created_at')
            ->paginate(15);

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless(
            $order->user_id === $request->user()->id || $request->user()->can('payments.create'),
            403
        );

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:USD,BS',
            'method' => 'required|in:pago_movil,transferencia,efectivo',
            'reference' => 'nullable|string|max:255',
            'receipt' => ['required_if:method,pago_movil,transferencia', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $amounts = Payment::computeAmounts($validated['method'], $validated['currency'], (float) $validated['amount']);

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('receipts', 'public')
            : null;

        $payment = $order->payments()->create([
            'amount_usd' => $amounts['amount_usd'],
            'amount_bs' => $amounts['amount_bs'],
            'exchange_rate' => $amounts['exchange_rate'],
            'currency' => $validated['currency'],
            'method' => $validated['method'],
            'reference' => $validated['reference'] ?? null,
            'receipt_path' => $receiptPath,
            'status' => 'pendiente',
            'submitted_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

        Notification::send(User::role(['Admin', 'Empleado'])->get(), new PaymentPendingVerification($payment));

        return back()->with('success', 'Pago registrado. Quedará pendiente de verificación.');
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'bank_deposit_id' => 'nullable|exists:bank_deposits,id',
        ]);

        abort_unless($payment->status === 'pendiente', 422);

        if (! empty($validated['bank_deposit_id'])) {
            $payment->update(['bank_deposit_id' => $validated['bank_deposit_id']]);
        }

        $payment->markVerified($request->user()->id);

        return back()->with('success', 'Pago verificado correctamente.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === 'pendiente', 422);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $payment->update([
            'status' => 'rechazado',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Pago rechazado.');
    }
}
