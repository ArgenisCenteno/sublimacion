<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:receipts.view')->only(['index']);
        $this->middleware('can:receipts.annul')->only(['void']);
    }

    public function index(Request $request): Response
    {
        $query = Receipt::with(['order']);

        if ($request->filled('status')) {
            $request->string('status') === 'anulado'
                ? $query->whereNotNull('voided_at')
                : $query->whereNull('voided_at');
        }

        $receipts = $query->orderBy('number', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return Inertia::render('Receipts/Index', [
            'receipts' => $receipts,
            'filters' => [
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function pdf(Request $request, Receipt $receipt): HttpResponse
    {
        $this->authorizeAccess($request, $receipt);

        $pdf = Pdf::loadView('receipts.pdf', [
            'receipt' => $receipt->load('order'),
            'paymentLabels' => [
                'pago_movil' => 'Pago móvil',
                'transferencia' => 'Transferencia',
                'efectivo' => 'Efectivo en tienda',
                'paypal' => 'PayPal',
            ],
        ]);

        return $pdf->stream("{$receipt->formatted_number}.pdf");
    }

    public function void(Request $request, Receipt $receipt): RedirectResponse
    {
        abort_if($receipt->voided_at !== null, 422, 'Este recibo ya está anulado.');

        $validated = $request->validate([
            'void_reason' => 'required|string|max:255',
        ]);

        $receipt->update([
            'voided_at' => now(),
            'voided_by' => $request->user()->id,
            'void_reason' => $validated['void_reason'],
        ]);

        activity()
            ->performedOn($receipt)
            ->causedBy($request->user())
            ->useLog('recibo')
            ->withProperties(['motivo' => $validated['void_reason']])
            ->log("Anuló recibo {$receipt->formatted_number}");

        return back()->with('success', 'Recibo anulado correctamente.');
    }

    private function authorizeAccess(Request $request, Receipt $receipt): void
    {
        abort_unless(
            $receipt->order->user_id === $request->user()->id || $request->user()->can('receipts.view'),
            403
        );
    }
}
