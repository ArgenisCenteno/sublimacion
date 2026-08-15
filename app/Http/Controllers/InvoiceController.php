<?php

namespace App\Http\Controllers;

use App\Models\CostEntry;
use App\Models\Invoice;
use App\Models\InvoiceVehicle;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Services\Invoices\InvoiceExtractor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:invoices.view')->only(['index', 'show']);
        $this->middleware('can:invoices.create')->only(['create', 'store']);
        $this->middleware('can:invoices.edit')->only(['edit', 'update']);
        $this->middleware('can:invoices.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $query = Invoice::with(['provider', 'uploadedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('provider', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        $invoices = $query->orderBy('issued_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => [
                'search'      => $request->string('search')->toString(),
                'provider_id' => $request->string('provider_id')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Invoices/Create', [
            'providers' => Provider::select('id', 'name')->orderBy('name')->get(),
            'vehicles'  => Vehicle::select('id', 'plate', 'brand', 'model')->orderBy('plate')->get(),
        ]);
    }

    public function store(Request $request, InvoiceExtractor $extractor): RedirectResponse
    {
        $request->validate([
            'provider_id' => 'nullable|exists:providers,id',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'file'        => 'required|file|mimes:jpg,jpeg,png,bmp,tiff,pdf',
            'file2'       => 'nullable|file|mimes:jpg,jpeg,png,bmp,tiff,pdf',
            'note'        => 'nullable|string|max:1000',
        ]);

        $file        = $request->file('file');
        $file2       = $request->file('file2');
        $path        = $file->store('invoices', 'public');
        $albaranPath = $file2 ? $file2->store('albaranes', 'public') : null;

        DB::beginTransaction();

        try {
            $data          = $extractor->extract(storage_path("app/public/{$path}"));
            $invoiceNumber = $data['invoice_number'] ?? null;

            if ($invoiceNumber && $request->vehicle_id) {
                $exists = InvoiceVehicle::whereHas('invoice', function ($q) use ($invoiceNumber) {
                    $q->where('invoice_number', $invoiceNumber);
                })->where('vehicle_id', $request->vehicle_id)->exists();

                if ($exists) {
                    DB::rollBack();
                    Storage::disk('public')->delete($path);
                    if ($albaranPath) {
                        Storage::disk('public')->delete($albaranPath);
                    }
                    return redirect()->back()->withErrors([
                        'file' => 'Ya existe una factura con ese numero para este vehiculo.',
                    ]);
                }
            }

            $totalAmount = is_numeric($data['total_amount'] ?? null) ? (float) $data['total_amount'] : 0;
            $subtotal    = is_numeric($data['subtotal_amount'] ?? null) ? (float) $data['subtotal_amount'] : 0;
            $vatAmount   = is_numeric($data['vat_amount'] ?? null) ? (float) $data['vat_amount'] : 0;

            $invoice = Invoice::create([
                'provider_id'         => $request->provider_id,
                'uploaded_by_user_id' => auth()->id(),
                'pdf_url'             => 'storage/' . $path,
                'albaran_url'         => $albaranPath ? 'storage/' . $albaranPath : null,
                'disk'                => 'public',
                'original_filename'   => $file->getClientOriginalName(),
                'ocr_status'          => 'pendiente',
                'status'              => 'pendiente',
                'total_amount'        => 0,
                'invoice_number'      => $invoiceNumber,
                'issued_at'           => $data['issued_at'] ?? null,
                'ocr_raw_text'        => $data['raw_text'] ?? null,
                'subtotal_amount'     => $subtotal,
                'vat_amount'          => $vatAmount,
            ]);

            $invoiceVehicle = null;

            if ($request->vehicle_id) {
                $invoiceVehicle = InvoiceVehicle::create([
                    'invoice_id'      => $invoice->id,
                    'vehicle_id'      => $request->vehicle_id,
                    'assigned_amount' => $totalAmount,
                    'assigned_by'     => 'ocr',
                    'notes'           => $request->note,
                ]);

                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $invoice->items()->create([
                            'quantity'    => $item['quantity'] ?? null,
                            'description' => $item['description'] ?? '',
                            'unit_price'  => $item['unit_price'] ?? 0,
                            'total_price' => $item['total_price'] ?? 0,
                        ]);
                    }
                }

                CostEntry::create([
                    'vehicle_id'         => $request->vehicle_id,
                    'invoice_vehicle_id' => $invoiceVehicle->id,
                    'type'               => 'pvi',
                    'amount'             => $totalAmount,
                    'concept'            => 'Factura ' . ($invoiceNumber ?? 'sin numero'),
                    'created_by_user_id' => auth()->id(),
                    'entry_date'         => $data['issued_at'] ?? Carbon::now()->toDateString(),
                    'notes'              => $request->note,
                ]);
            }

            $invoice->update([
                'total_amount' => $invoiceVehicle ? $invoiceVehicle->assigned_amount : $totalAmount,
                'ocr_status'   => 'procesado',
                'status'       => 'procesada',
            ]);

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Factura cargada y procesada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($path);

            Storage::disk('public')->delete($albaranPath ?? null);

            if ($albaranPath) {
                Storage::disk('public')->delete($albaranPath);
            }
            Log::error('InvoiceController@store: ' . $e->getMessage(), ['exception' => $e]);


            return redirect()->back()->withErrors([
                'file' => 'Ocurrio un error al procesar la factura: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['provider', 'uploadedBy', 'items', 'invoiceVehicles.vehicle', 'invoiceVehicles.cost_entries']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        return Inertia::render('Invoices/Edit', [
            'invoice'   => $invoice,
            'providers' => Provider::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'subtotal_amount' => 'nullable|numeric|min:0',
            'vat_amount'      => 'nullable|numeric|min:0',
            'total_amount'    => 'required|numeric|min:0',
        ]);

        $validated['total_amount'] =
            ($validated['subtotal_amount'] ?? 0) +
            ($validated['vat_amount'] ?? 0);

        $invoice->update($validated);

        return redirect()->route('invoices.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Factura eliminada correctamente.');
    }
}
