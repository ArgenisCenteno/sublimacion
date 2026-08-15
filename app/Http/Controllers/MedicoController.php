<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicoRequest;
use App\Http\Requests\UpdateMedicoRequest;
use App\Models\Medico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $query = Medico::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre',       'like', "%{$search}%")
                  ->orWhere('apellido',   'like', "%{$search}%")
                  ->orWhere('especialidad','like', "%{$search}%")
                  ->orWhere('telefono',   'like', "%{$search}%");
            });
        }

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        $medicos = $query
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $especialidades = Medico::distinct()
            ->orderBy('especialidad')
            ->whereNotNull('especialidad')
            ->pluck('especialidad');

        return Inertia::render('Catalogo/Medicos/Index', [
            'medicos'       => $medicos,
            'especialidades'=> $especialidades,
            'filters'       => $request->only(['search', 'especialidad', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalogo/Medicos/Create', [
            'especialidades' => Medico::distinct()
                ->orderBy('especialidad')
                ->whereNotNull('especialidad')
                ->pluck('especialidad'),
        ]);
    }

    public function store(StoreMedicoRequest $request): RedirectResponse
    {
        Medico::create($request->validated());

        return redirect()
            ->route('medicos.index')
            ->with('success', 'Médico registrado correctamente.');
    }

    public function show( $id): Response
    {
        $medico = Medico::findOrFail($id);
      //  $medico->load(['ordenes' => fn ($q) => $q->latest()->limit(10)]);

        return Inertia::render('Catalogo/Medicos/Show', [
            'medico' => $medico,
        ]);
    }

    public function edit( $id): Response
    {
         $medico = Medico::findOrFail($id);
        return Inertia::render('Catalogo/Medicos/Edit', [
            'medico'         => $medico,
            'especialidades' => Medico::distinct()
                ->orderBy('especialidad')
                ->whereNotNull('especialidad')
                ->pluck('especialidad'),
        ]);
    }

    public function update(UpdateMedicoRequest $request,  $id): RedirectResponse
    {
         $medico = Medico::findOrFail($id);
        $medico->update($request->validated());

        return redirect()
            ->route('medicos.show', $medico)
            ->with('success', 'Médico actualizado correctamente.');
    }

    public function destroy(Medico $medico): RedirectResponse
    {
        $medico->delete();

        return redirect()
            ->route('catalogo.medicos.index')
            ->with('success', 'Médico eliminado.');
    }

    public function toggleActivo(Medico $medico): RedirectResponse
    {
        $medico->update(['activo' => !$medico->activo]);

        return back()->with('success',
            $medico->activo ? 'Médico activado.' : 'Médico desactivado.'
        );
    }

    /**
     * Búsqueda rápida para el autocompletado en la orden de trabajo.
     */
    public function buscar(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->string('q'));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $medicos = Medico::where('activo', true)
            ->where(function ($q) use ($search) {
                $q->where('nombre',        'like', "%{$search}%")
                  ->orWhere('apellido',    'like', "%{$search}%")
                  ->orWhere('especialidad','like', "%{$search}%");
            })
            ->orderBy('apellido')
            ->limit(10)
            ->get(['id', 'nombre', 'apellido', 'especialidad', 'telefono']);

        return response()->json(
            $medicos->map(fn (Medico $m) => [
                'id'             => $m->id,
                'nombre_completo'=> $m->nombre_completo,
                'especialidad'   => $m->especialidad,
                'telefono'       => $m->telefono,
            ])
        );
    }
}