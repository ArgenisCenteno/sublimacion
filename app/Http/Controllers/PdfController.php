<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrdenTrabajo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
 

class PdfController extends Controller
{
    public function resultados(OrdenTrabajo $orden): Response
    {
        abort_unless(
            in_array($orden->estado, ['listo', 'entregado']),
            403,
            'La orden aún no tiene resultados validados.'
        );

        $orden->load([
            'paciente',
            'medico',
            'recepcionista',
            'items.examen.valoresReferencia',
            'items.procesadoPor',
        ]);

        // Genera el QR con la URL de verificación
        $urlVerificacion = url("/verificar/{$orden->codigo}");
        $qrSvg = null;

        // Serializa ítems con referencia del paciente
        $items = $orden->items->map(function ($item) use ($orden) {
            $referencia = $item->examen->referenciaParaPaciente(
                $orden->paciente->edad,
                $orden->paciente->sexo
            );
           return (object) [
    'id'               => $item->id,
    'nombre'           => $item->examen->nombre,
    'codigo'           => $item->examen->codigo,
    'unidad'           => $item->examen->unidad,
    'categoria'        => $item->examen->categoria,      // ← agregar
    'subcategoria'     => $item->examen->subcategoria,   // ← agregar
    'orden_col'        => $item->examen->orden_col,      // ← agregar
    'orden_fila'       => $item->examen->orden_fila,     // ← agregar
    'resultado_valor'  => $item->resultado_valor,
    'resultado_texto'  => $item->resultado_texto,
    'fuera_de_rango'   => $item->fuera_de_rango,
    'indicador'        => $item->indicador,
    'procesado_por'    => $item->procesadoPor?->name,
    'procesado_at'     => $item->procesado_at,
    'referencia'       => $referencia ? (object) [
        'valor_min'   => $referencia->valor_min,
        'valor_max'   => $referencia->valor_max,
        'valor_texto' => $referencia->valor_texto,
        'etiqueta'    => $referencia->etiqueta,
    ] : null,
];
        });

        $pdf = Pdf::loadView('pdf.resultados', [
            'orden'   => $orden,
            'items'   => $items,
            'qrSvg'   => $qrSvg,
            'fechaImpresion' => now()->format('d/m/Y H:i'),
        ])
        ->setPaper('letter', 'portrait');

        return $pdf->stream("resultados-{$orden->codigo}.pdf");
    }

    public function orden(OrdenTrabajo $orden): Response
    {
        $orden->load(['paciente', 'medico', 'recepcionista', 'items.examen']);

        $urlVerificacion = url("/verificar/{$orden->codigo}");
        $qrSvg = base64_encode(
            QrCode::format('svg')
                ->size(120)
                ->margin(0)
                ->generate($urlVerificacion)
        );

        $pdf = Pdf::loadView('pdf.orden', [
            'orden'          => $orden,
            'qrSvg'          => $qrSvg,
            'fechaImpresion' => now()->format('d/m/Y H:i'),
        ])
        ->setPaper('letter', 'portrait');

        return $pdf->stream("orden-{$orden->codigo}.pdf");
    }
}