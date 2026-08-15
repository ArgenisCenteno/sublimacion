<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrdenExamen;
use App\Models\OrdenTrabajo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ResultadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cola de órdenes con muestras tomadas listas para cargar resultados.
     */
    public function index(Request $request): Response
    {
        $query = OrdenTrabajo::with(['paciente', 'medico'])
            ->whereIn('estado', ['en_proceso', 'pendiente'])
            ->whereHas('items', fn ($q) =>
                $q->whereIn('estado', ['muestra_tomada', 'en_proceso'])
            )
            ->withCount([
                'items as items_pendientes_count' => fn ($q) =>
                    $q->whereIn('estado', ['muestra_tomada', 'en_proceso']),
                'items as items_listos_count' => fn ($q) =>
                    $q->where('estado', 'listo'),
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

        return Inertia::render('Laboratorio/Resultados/Index', [
            'ordenes' => $ordenes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Vista de carga de resultados para una orden.
     */
    public function show(OrdenTrabajo $orden): Response
    {
        $orden->load([
            'paciente',
            'medico',
            'recepcionista',
            'items.examen.valoresReferencia',
            'items.procesadoPor',
        ]);

        // Serializa ítems con su referencia para el paciente
        $items = $orden->items->map(function ($item) use ($orden) {
            $data = $item->toArray();

            $referencia = $item->examen->referenciaParaPaciente(
                $orden->paciente->edad,
                $orden->paciente->sexo
            );

            $data['referencia'] = $referencia ? [
                'valor_min'   => $referencia->valor_min,
                'valor_max'   => $referencia->valor_max,
                'valor_texto' => $referencia->valor_texto,
                'etiqueta'    => $referencia->etiqueta,
            ] : null;

            return $data;
        });

        return Inertia::render('Laboratorio/Resultados/Show', [
            'orden' => array_merge($orden->toArray(), [
                'items'              => $items,
                'estado_label'       => $orden->estado_label,
                'estado_pago_label'  => $orden->estado_pago_label,
            ]),
        ]);
    }

    /**
     * Carga el resultado de un ítem individual.
     */
    public function cargarResultado(Request $request, $id): RedirectResponse
    {
       
        $item = OrdenExamen::findOrFail($id);
       
        $orden = $item->orden;
        abort_unless($item->orden_trabajo_id === $orden->id, 404);
        abort_unless(
            in_array($item->estado, ['muestra_tomada', 'en_proceso']),
            403,
            'Este examen no está listo para cargar resultados.'
        );

        $request->validate([
            'resultado_valor' => 'nullable|numeric',
            'resultado_texto' => 'nullable|string|max:200',
        ], [
            'resultado_valor.numeric' => 'El resultado debe ser un número.',
        ]);

        DB::transaction(function () use ($request, $item, $orden) {
            $item->update([
                'resultado_valor'  => $request->resultado_valor,
                'resultado_texto'  => $request->resultado_texto,
                'resultado_unidad' => $item->examen->unidad,
                'estado'           => 'en_proceso',
                'procesado_por'    => auth()->id(),
                'procesado_at'     => now(),
            ]);

            // Evalúa si está fuera de rango
            $item->load('examen.valoresReferencia', 'orden.paciente');
            $item->evaluarRango();
        });

        return back()->with('success', "Resultado de {$item->examen->nombre} guardado.");
    }

    /**
     * Carga todos los resultados de una orden de una sola vez.
     */
    public function cargarTodos(Request $request, OrdenTrabajo $orden): RedirectResponse
    {
        $request->validate([
            'resultados'                    => 'required|array',
            'resultados.*.item_id'          => 'required|exists:orden_examen,id',
            'resultados.*.resultado_valor'  => 'nullable|numeric',
            'resultados.*.resultado_texto'  => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($request, $orden) {
            foreach ($request->resultados as $resultado) {
                $item = OrdenExamen::findOrFail($resultado['item_id']);

                abort_unless($item->orden_trabajo_id === $orden->id, 403);

                if ($resultado['resultado_valor'] === null && empty($resultado['resultado_texto'])) {
                    continue; // Salta los vacíos
                }

                $item->update([
                    'resultado_valor'  => $resultado['resultado_valor'] ?? null,
                    'resultado_texto'  => $resultado['resultado_texto'] ?? null,
                    'resultado_unidad' => $item->examen->unidad,
                    'estado'           => 'en_proceso',
                    'procesado_por'    => auth()->id(),
                    'procesado_at'     => now(),
                ]);

                $item->load('examen.valoresReferencia', 'orden.paciente');
                $item->evaluarRango();
            }
        });

        return back()->with('success', 'Resultados guardados correctamente.');
    }

    /**
     * Valida y firma todos los resultados — pasa la orden a "listo".
     */
    public function validar(OrdenTrabajo $orden): RedirectResponse
    {
        // Verifica que todos los ítems tengan resultado
        $sinResultado = $orden->items()
            ->whereIn('estado', ['pendiente', 'muestra_tomada'])
            ->count();

        if ($sinResultado > 0) {
            return back()->withErrors([
                'validacion' => "Faltan {$sinResultado} exámen(es) sin resultado. Carga todos antes de validar.",
            ]);
        }

        DB::transaction(function () use ($orden) {
            // Marca todos los ítems como listos
            $orden->items()->update([
                'estado'        => 'listo',
                'procesado_por' => auth()->id(),
                'procesado_at'  => now(),
            ]);

            // Marca la orden como lista
            $orden->update(['estado' => 'listo']);

            // Marca las muestras como procesadas
            $orden->muestras()->whereIn('estado', ['tomada', 'en_proceso'])
                  ->update(['estado' => 'procesada']);
        });

        return back()->with('success', 'Resultados validados. La orden está lista para entregar.');
    }

    /**
     * Descarta el resultado de un ítem y lo vuelve a muestra_tomada.
     */
    public function descartarResultado(OrdenTrabajo $orden, OrdenExamen $item): RedirectResponse
    {
        abort_unless($item->orden_trabajo_id === $orden->id, 404);
        abort_unless($orden->estado !== 'listo', 403, 'No se puede modificar una orden ya validada.');

        $item->update([
            'resultado_valor' => null,
            'resultado_texto' => null,
            'fuera_de_rango'  => false,
            'indicador'       => null,
            'estado'          => 'muestra_tomada',
            'procesado_por'   => null,
            'procesado_at'    => null,
        ]);

        return back()->with('success', 'Resultado descartado.');
    }
}