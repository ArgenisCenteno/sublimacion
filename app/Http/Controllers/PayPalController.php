<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['webhook']);
    }

    public function createOrder(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->payment_method === 'paypal', 422, 'Este pedido no usa PayPal como método de pago.');

        try {
            $provider = $this->client();
            $provider->getAccessToken();

            $response = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($order->balanceDue(), 2, '.', ''),
                    ],
                ]],
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal createOrder threw', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'PayPal no está configurado correctamente todavía.'], 422);
        }

        if (empty($response['id'])) {
            Log::error('PayPal createOrder failed', ['order_id' => $order->id, 'response' => $response]);

            return response()->json(['error' => 'No se pudo iniciar el pago con PayPal.'], 422);
        }

        Payment::create([
            'order_id' => $order->id,
            'amount_usd' => $order->balanceDue(),
            'method' => 'paypal',
            'status' => 'pendiente',
            'paypal_order_id' => $response['id'],
            'submitted_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

        return response()->json(['id' => $response['id']]);
    }

    public function captureOrder(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'paypal_order_id' => 'required|string',
        ]);

        $payment = Payment::where('order_id', $order->id)
            ->where('paypal_order_id', $validated['paypal_order_id'])
            ->where('method', 'paypal')
            ->firstOrFail();

        if ($payment->status === 'verificado') {
            return response()->json(['status' => 'already_captured']);
        }

        try {
            $provider = $this->client();
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($validated['paypal_order_id']);
        } catch (\Throwable $e) {
            Log::error('PayPal captureOrder threw', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'PayPal no está configurado correctamente todavía.'], 422);
        }

        if (($response['status'] ?? null) !== 'COMPLETED') {
            Log::warning('PayPal capture not completed', ['order_id' => $order->id, 'response' => $response]);

            return response()->json(['error' => 'El pago no pudo completarse.'], 422);
        }

        $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        $payment->update(['paypal_transaction_id' => $captureId]);
        $payment->markVerified();

        return response()->json(['status' => 'completed']);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        try {
            if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
                $this->handleCaptureCompleted($resource);
            } elseif ($eventType === 'PAYMENT.CAPTURE.DENIED') {
                $this->handleCaptureDenied($resource);
            }
        } catch (\Throwable $e) {
            Log::error('PayPal webhook processing failed', ['error' => $e->getMessage(), 'payload' => $payload]);
        }

        // Siempre 200 para que PayPal no reintente indefinidamente.
        return response()->json(['received' => true]);
    }

    private function handleCaptureCompleted(array $resource): void
    {
        $captureId = $resource['id'] ?? null;
        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        $payment = Payment::where('paypal_transaction_id', $captureId)
            ->orWhere('paypal_order_id', $paypalOrderId)
            ->first();

        if (! $payment || $payment->status === 'verificado') {
            return;
        }

        $payment->update(['paypal_transaction_id' => $captureId]);
        $payment->markVerified();
    }

    private function handleCaptureDenied(array $resource): void
    {
        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        $payment = Payment::where('paypal_order_id', $paypalOrderId)->first();

        if (! $payment || $payment->status !== 'pendiente') {
            return;
        }

        $payment->update([
            'status' => 'rechazado',
            'rejection_reason' => 'Pago denegado por PayPal.',
            'verified_at' => now(),
        ]);
    }

    private function client(): PayPalClient
    {
        return new PayPalClient(config('paypal'));
    }
}
