<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\Pago;
use App\Models\TasaCambio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Index global ─────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $base = Pago::with(['orden.paciente', 'cajero', 'metodos'])
            ->withCount('metodos');

        // Aplicar los mismos filtros al query principal y a los totales
        $applyFilters = function ($q) use ($request) {
            if ($request->filled('search')) {
                $s = trim($request->search);
                $q->whereHas(
                    'orden',
                    fn($o) =>
                    $o->where('codigo', 'like', "%{$s}%")
                        ->orWhereHas(
                            'paciente',
                            fn($p) =>
                            $p->where('nombres', 'like', "%{$s}%")
                                ->orWhere('apellidos', 'like', "%{$s}%")
                                ->orWhere('cedula', 'like', "%{$s}%")
                        )
                );
            }
            if ($request->filled('divisa'))
                $q->where('divisa', $request->divisa);
            if ($request->filled('estado'))
                $q->where('estado', $request->estado);
            if ($request->filled('fecha'))
                $q->whereDate('created_at', $request->fecha);
        };

        $applyFilters($base);

        $pagos = (clone $base)->latest()->paginate(25)->withQueryString();

        $totales = Pago::selectRaw("
    COUNT(*)                                                    AS total,
    SUM(CASE WHEN estado = 'activo'  THEN 1    ELSE 0 END)     AS activos,
    SUM(CASE WHEN estado = 'anulado' THEN 1    ELSE 0 END)     AS anulados,
    SUM(CASE WHEN estado = 'activo'  THEN monto_usd ELSE 0 END) AS suma_usd
");
        $applyFilters($totales);
        $totales = $totales->first();

        return Inertia::render('Recepcion/Pagos/Index', [
            'pagos' => $pagos,
            'totales' => $totales,
            'filters' => $request->only(['search', 'divisa', 'estado', 'fecha']),
        ]);
    }

    // ── Pantalla de cobro ────────────────────────────────────────────────────

    public function create(OrdenTrabajo $orden): Response
    {
        abort_unless(
            in_array($orden->estado, ['pendiente', 'en_proceso', 'listo']),
            403,
            'No se puede cobrar una orden anulada o ya entregada.'
        );

        $orden->load([
            'paciente',
            'medico',
            'items.examen',
            'pagos' => function ($q) {
                $q->where('estado', 'activo')->with(['cajero', 'metodos']);
            }
        ]);

        return Inertia::render('Recepcion/Pagos/Create', [
            'orden' => array_merge($orden->toArray(), [
                'estado_label' => $orden->estado_label,
                'estado_pago_label' => $orden->estado_pago_label,
                'monto_pagado_usd' => round($orden->monto_pagado_usd, 2),
                'saldo_pendiente' => round($orden->saldo_pendiente_usd, 2),
            ]),
            'tasas' => [
                'EUR' => TasaCambio::vigente('EUR'),
                'BS' => TasaCambio::vigente('BS'),
            ],
        ]);
    }

    // ── Registrar pago ───────────────────────────────────────────────────────

    public function store(Request $request, OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless(in_array($orden->estado, ['pendiente', 'en_proceso', 'listo']), 403);

        $data = $request->validate([
            'divisa' => ['required', 'in:USD,EUR,BS'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'metodos' => ['required', 'array', 'min:1'],
            'metodos.*.metodo' => ['required', 'in:efectivo,transferencia,punto_de_venta,zelle,pago_movil,otro'],
            'metodos.*.monto_divisa' => ['required', 'numeric', 'min:0.01'],
            'metodos.*.referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $tasa = TasaCambio::vigente($data['divisa']);
        abort_unless($tasa !== null, 422, "No hay tasa configurada para {$data['divisa']}.");

        DB::transaction(function () use ($data, $orden, $tasa) {
            $totalDivisa = collect($data['metodos'])->sum('monto_divisa');
            $totalUsd = round($totalDivisa / $tasa, 4);

            $pago = Pago::create([
                'orden_trabajo_id' => $orden->id,
                'user_id' => auth()->id(),
                'divisa' => $data['divisa'],
                'monto_divisa' => $totalDivisa,
                'tasa_cambio' => $tasa,
                'monto_usd' => $totalUsd,
                'estado' => 'activo',
                'referencia' => $data['referencia'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['metodos'] as $m) {
                $pago->metodos()->create([
                    'metodo' => $m['metodo'],
                    'monto_divisa' => $m['monto_divisa'],
                    'monto_usd' => round($m['monto_divisa'] / $tasa, 4),
                    'referencia' => $m['referencia'] ?? null,
                ]);
            }

            $orden->recalcularEstadoPago();
        });

        return back()->with('success', 'Pago registrado correctamente.');
    }

    // ── Anular pago ──────────────────────────────────────────────────────────

    public function anular(OrdenTrabajo $orden, Pago $pago): RedirectResponse
    {
       
        abort_unless($pago->orden_trabajo_id === $orden->id, 404);
        abort_unless($pago->estado === 'activo', 403, 'El pago ya está anulado.');
        
        DB::transaction(function () use ($pago, $orden) {
            $pago->update([
                'estado' => 'anulado',
                'anulado_by' => auth()->id(),
                'anulado_at' => now(),
            ]);

            // monto_pagado_usd filtra solo estado='activo',
            // así que recalcular aquí ya refleja el saldo correcto.
            $orden->recalcularEstadoPago();
        });

        return back()->with('success', 'Pago anulado. El saldo de la orden ha sido actualizado.');
    }

    // ── Eliminar (hard delete, solo admin) ───────────────────────────────────

    public function destroy(OrdenTrabajo $orden, Pago $pago): RedirectResponse
    {
        abort_unless($pago->orden_trabajo_id === $orden->id, 404);
        abort_unless(
            in_array($orden->estado_pago, ['pendiente', 'parcial']),
            403,
            'No se puede eliminar un pago de una orden ya liquidada.'
        );

        DB::transaction(function () use ($pago, $orden) {
            $pago->metodos()->delete();
            $pago->delete();
            $orden->recalcularEstadoPago();
        });

        return back()->with('success', 'Pago eliminado.');
    }

    public function show( $id): Response
    {
        $pago = Pago::findOrFail($id);
 
        $pago->load([
            'cajero',
            'anuladoPor',
            'metodos',
            'orden.paciente',
            'orden.medico',
        ]);

        // Añadir labels y saldo al array de la orden
        $ordenData = $pago->orden;
        
        $ordenData['estado_label'] = $pago->orden->estado_label;
        $ordenData['estado_pago_label'] = $pago->orden->estado_pago_label;
        $ordenData['monto_pagado_usd'] = round($pago->orden->monto_pagado_usd, 2);
        $ordenData['saldo_pendiente'] = round($pago->orden->saldo_pendiente_usd, 2);

        $pagoData = $pago->toArray();
        $pagoData['orden'] = $ordenData;

        // Renombrar anulado_por para que coincida con la interfaz TS
        $pagoData['anulado_por'] = $pago->anuladoPor?->only(['id', 'name']);

        return Inertia::render('Recepcion/Pagos/Show', [
            'pago' => $pagoData,
        ]);
    }
}