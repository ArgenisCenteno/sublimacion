<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePerfilRequest;
use App\Http\Requests\UpdatePerfilRequest;
use App\Models\Examen;
use App\Models\Perfil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $query = Perfil::withCount('examenes');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre',    'like', "%{$search}%")
                  ->orWhere('codigo',  'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        $perfiles = $query
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $categorias = Perfil::distinct()
            ->orderBy('categoria')
            ->whereNotNull('categoria')
            ->pluck('categoria');

        return Inertia::render('Catalogo/Perfiles/Index', [
            'perfiles'   => $perfiles,
            'categorias' => $categorias,
            'filters'    => $request->only(['search', 'categoria', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalogo/Perfiles/Create', [
            'examenes'   => Examen::where('activo', true)
                                ->orderBy('categoria')
                                ->orderBy('nombre')
                                ->get(['id', 'nombre', 'codigo', 'categoria', 'precio', 'unidad']),
            'categorias' => Perfil::distinct()
                                ->orderBy('categoria')
                                ->whereNotNull('categoria')
                                ->pluck('categoria'),
        ]);
    }

    public function store(StorePerfilRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $perfil = Perfil::create($request->safe()->except('examenes_ids'));

            if (!empty($request->validated()['examenes_ids'])) {
                $perfil->examenes()->sync($request->validated()['examenes_ids']);
            }
        });

        return redirect()
            ->route('perfiles.index')
            ->with('success', 'Perfil registrado correctamente.');
    }

    public function show($id): Response
    {
        $perfil = Perfil::findOrFail($id);
        $perfil->load('examenes');

        return Inertia::render('Catalogo/Perfiles/Show', [
            'perfil' => $this->serializePerfil($perfil),
        ]);
    }

    public function edit( $id): Response
    {
        $perfil = Perfil::findOrFail($id);
        $perfil->load('examenes');

        return Inertia::render('Catalogo/Perfiles/Edit', [
            'perfil'     => $this->serializePerfil($perfil),
            'examenes'   => Examen::where('activo', true)
                                ->orderBy('categoria')
                                ->orderBy('nombre')
                                ->get(['id', 'nombre', 'codigo', 'categoria', 'precio', 'unidad']),
            'categorias' => Perfil::distinct()
                                ->orderBy('categoria')
                                ->whereNotNull('categoria')
                                ->pluck('categoria'),
        ]);
    }

    public function update(UpdatePerfilRequest $request, $id): RedirectResponse
    {
        $perfil = Perfil::findOrFail($id);

        DB::transaction(function () use ($request, $perfil) {
            $perfil->update($request->safe()->except('examenes_ids'));

            // sync reemplaza los exámenes existentes con los nuevos
            $perfil->examenes()->sync($request->validated()['examenes_ids'] ?? []);
        });

        return redirect()
            ->route('perfiles.show', $perfil)
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(Perfil $perfil): RedirectResponse
    {
        $perfil->delete();

        return redirect()
            ->route('perfiles.index')
            ->with('success', 'Perfil eliminado.');
    }

    public function toggleActivo(Perfil $perfil): RedirectResponse
    {
        $perfil->update(['activo' => !$perfil->activo]);

        return back()->with('success',
            $perfil->activo ? 'Perfil activado.' : 'Perfil desactivado.'
        );
    }

    private function serializePerfil(Perfil $perfil): array
    {
        $data = $perfil->toArray();

        // Agrega los accessors que no vienen en toArray()
        $data['precio_individual'] = $perfil->precio_individual;
        $data['ahorro']            = $perfil->ahorro;

        return $data;
    }
}