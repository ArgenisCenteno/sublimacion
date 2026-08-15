<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->query('start_date');
        $end   = $request->query('end_date');

        $invoices = Invoice::whereBetween('issued_at', [$start, $end])
            ->with('provider', 'uploadedBy')
            ->get();

        $rows   = [];
        $rows[] = ['Numero', 'Proveedor', 'Usuario', 'Subtotal', 'IVA', 'Total', 'Status'];

        foreach ($invoices as $inv) {
            $rows[] = [
                $inv->invoice_number,
                $inv->provider->name ?? '-',
                $inv->uploadedBy->name ?? '-',
                $inv->subtotal_amount,
                $inv->vat_amount,
                $inv->total_amount,
                $inv->status,
            ];
        }

        $filename = "invoices_{$start}_to_{$end}.csv";
        $handle   = fopen('php://memory', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return Response::streamDownload(function () use ($handle) {
            fpassthru($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportZip(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->query('start_date');
        $end   = $request->query('end_date');

        $invoices = Invoice::whereBetween('issued_at', [$start, $end])->get();

        $zipName = "invoices_{$start}_to_{$end}.zip";
        $zipPath = storage_path("app/private/{$zipName}");

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el ZIP.');
        }

        foreach ($invoices as $inv) {
            if ($inv->pdf_url) {
                $relative = str_replace('storage/', '', $inv->pdf_url);
                if (Storage::disk('public')->exists($relative)) {
                    $zip->addFile(
                        Storage::disk('public')->path($relative),
                        'facturas/' . basename($relative)
                    );
                }
            }

            if ($inv->albaran_url) {
                $relative = str_replace('storage/', '', $inv->albaran_url);
                if (Storage::disk('public')->exists($relative)) {
                    $zip->addFile(
                        Storage::disk('public')->path($relative),
                        'albaranes/' . basename($relative)
                    );
                }
            }
        }

        $zip->close();

        if (!file_exists($zipPath)) {
            abort(500, 'El ZIP no se creo correctamente.');
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
