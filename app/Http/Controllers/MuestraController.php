<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Muestra;
use App\Models\OrdenTrabajo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MuestraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lista de órdenes pendientes de toma de muestra.
     */
    public function index(Request $request): Response
    {
        $query = OrdenTrabajo::with(['paciente', 'muestras'])
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->withCount(['muestras as muestras_pendientes_count' => fn ($q) =>
                $q->where('estado', 'pendiente')
            ]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhereHas('paciente', fn ($p) =>
                        $p->where('nombres',   'like', "%{$search}%")
                          ->orWhere('apellidos','like', "%{$search}%")
                          ->orWhere('cedula',   'like', "%{$search}%")
                  );
            });
        }

        $ordenes = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Laboratorio/Muestras/Index', [
            'ordenes' => $ordenes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Vista de toma de muestras para una orden específica.
     */
    public function show(OrdenTrabajo $orden): Response
    {
        $orden->load([
            'paciente',
            'medico',
            'muestras.tomadaPor',
            'muestras.items.examen',
            'items.examen',
        ]);

        return Inertia::render('Laboratorio/Muestras/Show', [
            'orden' => $orden,
        ]);
    }

    /**
     * Genera las muestras agrupando los exámenes por tipo de muestra.
     * Se llama una sola vez al abrir la orden en el módulo de muestras.
     */
    public function generar(OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless(
            in_array($orden->estado, ['pendiente', 'en_proceso']),
            403,
            'No se pueden generar muestras para esta orden.'
        );

        // Solo genera si no hay muestras aún
        if ($orden->muestras()->exists()) {
            return back()->with('warning', 'Esta orden ya tiene muestras generadas.');
        }

        DB::transaction(function () use ($orden) {
            $orden->load('items.examen');

            // Agrupa los ítems por tipo de muestra + tubo
            $grupos = $orden->items->groupBy(fn ($item) =>
                $item->examen->muestra . '|' . ($item->examen->tubo ?? '')
            );

            foreach ($grupos as $clave => $items) {
                [$tipo, $tubo] = explode('|', $clave);

                $muestra = $orden->muestras()->create([
                    'tipo'  => $tipo,
                    'tubo'  => $tubo ?: null,
                    'estado'=> 'pendiente',
                ]);

                // Asocia los ítems a esta muestra
                $muestra->items()->attach($items->pluck('id'));
            }
        });

        return back()->with('success', 'Muestras generadas correctamente.');
    }

    /**
     * Registra la toma de una muestra específica.
     */
    public function registrarToma(Request $request, OrdenTrabajo $orden, Muestra $muestra): RedirectResponse
    {
        abort_unless($muestra->orden_trabajo_id === $orden->id, 404);
        abort_unless($muestra->estado === 'pendiente', 403, 'Esta muestra ya fue procesada.');

        $request->validate([
            'observaciones' => 'nullable|string|max:300',
        ]);

        $muestra->update(['observaciones' => $request->observaciones]);
        $muestra->registrarToma(auth()->id());

        return back()->with('success', "Muestra de {$muestra->tipo} registrada correctamente.");
    }

    /**
     * Rechaza una muestra — vuelve a pendiente para retomar.
     */
    public function rechazar(Request $request, OrdenTrabajo $orden, Muestra $muestra): RedirectResponse
    {
        abort_unless($muestra->orden_trabajo_id === $orden->id, 404);
        abort_unless(in_array($muestra->estado, ['pendiente', 'tomada']), 403);

        $request->validate([
            'motivo_rechazo' => 'required|string|max:200',
        ]);

        $muestra->rechazar($request->motivo_rechazo);

        return back()->with('success', 'Muestra rechazada. Los exámenes volvieron a pendiente.');
    }

    /**
     * Marca todas las muestras pendientes de una orden como tomadas de una sola vez.
     */
    public function tomarTodas(OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless(in_array($orden->estado, ['pendiente', 'en_proceso']), 403);

        $muestras = $orden->muestras()->where('estado', 'pendiente')->get();

        abort_if($muestras->isEmpty(), 422, 'No hay muestras pendientes.');

        DB::transaction(function () use ($muestras) {
            foreach ($muestras as $muestra) {
                $muestra->registrarToma(auth()->id());
            }
        });

        return back()->with('success', 'Todas las muestras registradas correctamente.');
    }
}