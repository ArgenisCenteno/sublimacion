<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\StoreSetting;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['verify']);

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

        $receipt->load(['order.items', 'payment']);
        $order = $receipt->order;
        $settings = StoreSetting::current();

        $pdf = Pdf::loadView('receipts.pdf', [
            'receipt' => $receipt,
            'order' => $order,
            'store' => $settings,
            'logo' => $this->logoDataUri($settings->logo_path),
            'qr' => $this->qrDataUri($receipt->verificationUrl()),
            'amountPaid' => $order->amountPaid(),
            'balanceDue' => $order->balanceDue(),
            'paymentLabels' => self::PAYMENT_LABELS,
        ])->setPaper('letter')->setOption('isFontSubsettingEnabled', true);

        return $pdf->stream("{$receipt->formatted_number}.pdf");
    }

    /**
     * Página pública a la que lleva el QR: confirma que el recibo existe y su estado.
     */
    public function verify(Receipt $receipt): View
    {
        return view('receipts.verify', [
            'receipt' => $receipt->load('order'),
            'store' => StoreSetting::current(),
            'paymentLabels' => self::PAYMENT_LABELS,
        ]);
    }

    private const PAYMENT_LABELS = [
        'pago_movil' => 'Pago móvil',
        'transferencia' => 'Transferencia',
        'efectivo' => 'Efectivo en tienda',
        'paypal' => 'PayPal',
    ];

    private function qrDataUri(string $text): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(240, 1), new SvgImageBackEnd()));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($text));
    }

    // DomPDF no lee /storage por URL; se incrusta el logo como data URI
    private function logoDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return "data:{$mime};base64,".base64_encode(Storage::disk('public')->get($path));
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
