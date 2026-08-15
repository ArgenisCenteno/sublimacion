<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdenRequest;
use App\Http\Requests\UpdateOrdenRequest;
use App\Models\Examen;
use App\Models\Medico;
use App\Models\OrdenTrabajo;
use App\Models\Paciente;
use App\Models\Perfil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrdenTrabajoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $query = OrdenTrabajo::with(['paciente', 'medico', 'recepcionista'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas(
                        'paciente',
                        fn($p) =>
                        $p->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('cedula', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $ordenes = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Recepcion/Ordenes/Index', [
            'ordenes' => $ordenes,
            'filters' => $request->only(['search', 'estado', 'estado_pago', 'fecha']),
        ]);
    }

    public function create(Request $request): Response
    {

        // Si viene paciente_id desde el listado de pacientes
        $paciente = $request->filled('paciente_id')
            ? Paciente::findOrFail($request->paciente_id)
            : null;

        return Inertia::render('Recepcion/Ordenes/Create', [
            'paciente' => $paciente,
            'examenes' => Examen::where('activo', true)
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'categoria', 'precio', 'tiempo_resultado']),
            'perfiles' => Perfil::where('activo', true)
                ->with('examenes:id,nombre,codigo,precio')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'precio']),
            'medicos' => Medico::where('activo', true)
                ->orderBy('apellido')
                ->get(['id', 'nombre', 'apellido', 'especialidad']),
        ]);
    }

    public function store(StoreOrdenRequest $request): RedirectResponse
    {
       
        $orden = DB::transaction(function () use ($request) {
            $items = $request->validated()['items'];
            $subtotal = collect($items)->sum('precio');

            $orden = OrdenTrabajo::create([
                'codigo' => OrdenTrabajo::generarCodigo(),
                'paciente_id' => $request->paciente_id,
                'medico_id' => $request->medico_id,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'descuento' => $request->descuento ?? 0,
                'total' => max(0, $subtotal - ($request->descuento ?? 0)),
                'observaciones' => $request->observaciones,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,

            ]);

            foreach ($items as $item) {
                $orden->items()->create([
                    'examen_id' => $item['examen_id'],
                    'precio' => $item['precio'],
                ]);
            }

            return $orden;
        });

        return redirect()
            ->route('ordenes.show', $orden)
            ->with('success', "Orden {$orden->codigo} creada correctamente.");
    }

    public function show($id): Response
    {
        $orden = OrdenTrabajo::findOrFail($id);
        $orden->load([
            'paciente',
            'medico',
            'recepcionista',
            'items.examen.valoresReferencia',
        ]);

        return Inertia::render('Recepcion/Ordenes/Show', [
            'orden' => $this->serializarOrden($orden),
        ]);
    }

    public function edit($id): Response
    {
        $orden = OrdenTrabajo::findOrFail($id);


        // Solo se puede editar si está pendiente
        abort_unless($orden->estado === 'pendiente', 403, 'Solo se pueden editar órdenes pendientes.');

        $orden->load('items.examen');

        return Inertia::render('Recepcion/Ordenes/Edit', [
            'orden' => $orden,
            'examenes' => Examen::where('activo', true)
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'categoria', 'precio', 'tiempo_resultado']),
            'medicos' => Medico::where('activo', true)
                ->orderBy('apellido')
                ->get(['id', 'nombre', 'apellido', 'especialidad']),
        ]);
    }

    public function update(UpdateOrdenRequest $request, $id): RedirectResponse
    {
        $orden = OrdenTrabajo::findOrFail($id);

        abort_unless($orden->estado === 'pendiente', 403);

        DB::transaction(function () use ($request, $orden) {
            $items = $request->validated()['items'];
            $subtotal = collect($items)->sum('precio');

            $orden->update([
                'medico_id' => $request->medico_id,
                'descuento' => $request->descuento ?? 0,
                'subtotal' => $subtotal,
                'total' => max(0, $subtotal - ($request->descuento ?? 0)),
                'observaciones' => $request->observaciones,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
            ]);

            // Reemplaza los ítems
            $orden->items()->delete();
            foreach ($items as $item) {
                $orden->items()->create([
                    'examen_id' => $item['examen_id'],
                    'precio' => $item['precio'],
                ]);
            }
        });

        return redirect()
            ->route('ordenes.show', $orden)
            ->with('success', 'Orden actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless(
            in_array($orden->estado, ['pendiente', 'anulado']),
            403,
            'Solo se pueden eliminar órdenes pendientes o anuladas.'
        );

        $orden->delete();

        return redirect()
            ->route('ordenes.index')
            ->with('success', 'Orden eliminada.');
    }

    /**
     * Cambia el estado de pago a pagado.
     */
    public function marcarPagado(OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless($orden->estado_pago === 'pendiente', 403);

        $orden->update(['estado_pago' => 'pagado']);

        return back()->with('success', 'Orden marcada como pagada.');
    }

    /**
     * Anula la orden.
     */
    public function anular(OrdenTrabajo $orden): RedirectResponse
    {
        abort_unless(
            in_array($orden->estado, ['pendiente', 'en_proceso']),
            403,
            'Solo se pueden anular órdenes pendientes o en proceso.'
        );

        $orden->update(['estado' => 'anulado']);

        return back()->with('success', 'Orden anulada.');
    }

    /**
     * Marca la orden como entregada al paciente.
     */
    public function marcarEntregado($id): RedirectResponse
    {
        $orden = OrdenTrabajo::findOrFail($id);
        abort_unless($orden->estado === 'listo', 403, 'La orden debe estar lista para ser entregada.');

        $orden->update([
            'estado' => 'entregado',
            'fecha_entregado' => now(),
        ]);

        return back()->with('success', 'Resultados marcados como entregados.');
    }

    private function serializarOrden(OrdenTrabajo $orden): array
    {
        $data = $orden->toArray();

        $data['estado_label'] = $orden->estado_label;
        $data['estado_pago_label'] = $orden->estado_pago_label;

        // Serializa cada ítem con su examen y la referencia para este paciente
        $data['items'] = $orden->items->map(function ($item) use ($orden) {
            $itemData = $item->toArray();

            $referencia = $item->examen->referenciaParaPaciente(
                $orden->paciente->edad,
                $orden->paciente->sexo
            );

            $itemData['referencia'] = $referencia ? [
                'valor_min' => $referencia->valor_min,
                'valor_max' => $referencia->valor_max,
                'valor_texto' => $referencia->valor_texto,
                'etiqueta' => $referencia->etiqueta,
            ] : null;

            return $itemData;
        })->all();

        return $data;
    }
}