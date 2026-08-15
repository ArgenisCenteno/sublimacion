<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogo\StoreExamenRequest;
use App\Http\Requests\Catalogo\UpdateExamenRequest;
use App\Models\Examen;
use App\Models\ValorReferencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $query = Examen::withCount('valoresReferencia');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre',       'like', "%{$search}%")
                  ->orWhere('codigo',     'like', "%{$search}%")
                  ->orWhere('categoria',  'like', "%{$search}%")
                  ->orWhere('nombre_corto','like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        $examenes = $query->orderBy('categoria')->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $categorias = Examen::distinct()->orderBy('categoria')
            ->pluck('categoria');

        return Inertia::render('Catalogo/Examenes/Index', [
            'examenes'   => $examenes,
            'categorias' => $categorias,
            'filters'    => $request->only(['search', 'categoria', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalogo/Examenes/Create', [
            'categorias' => Examen::distinct()->orderBy('categoria')->pluck('categoria'),
        ]);
    }

    public function store(\App\Http\Requests\StoreExamenRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $examen = Examen::create($request->safe()->except('valores_referencia'));

            foreach ($request->validated()['valores_referencia'] ?? [] as $vr) {
                $examen->valoresReferencia()->create($vr);
            }
        });

        return redirect()
            ->route('examenes.index')
            ->with('success', 'Examen registrado correctamente.');
    }

    public function show($id): Response
    {
        $examen = Examen::findOrFail($id);
        $examen->load('valoresReferencia', 'perfiles');
        
        return Inertia::render('Catalogo/Examenes/Show', [
            'examen' => $examen,
        ]);
    }

    public function edit($id): Response
    {
         $examen = Examen::findOrFail($id);
        $examen->load('valoresReferencia');

        return Inertia::render('Catalogo/Examenes/Edit', [
            'examen'     => $examen,
            'categorias' => Examen::distinct()->orderBy('categoria')->pluck('categoria'),
        ]);
    }

    public function update(\App\Http\Requests\UpdateExamenRequest $request,  $id): RedirectResponse
    {
        $examen = Examen::findOrFail($id);
       
        DB::transaction(function () use ($request, $examen) {
            $examen->update($request->safe()->except('valores_referencia'));

            // Reemplaza todos los valores de referencia
            $examen->valoresReferencia()->delete();

            foreach ($request->validated()['valores_referencia'] ?? [] as $vr) {
                $examen->valoresReferencia()->create($vr);
            }
        });

        return redirect()
            ->route('examenes.show', $examen)
            ->with('success', 'Examen actualizado correctamente.');
    }

    public function destroy(Examen $examen): RedirectResponse
    {
        $examen->delete();

        return redirect()
            ->route('catalogo.examenes.index')
            ->with('success', 'Examen eliminado.');
    }

    /**
     * Toggle rápido de activo/inactivo desde el Index
     * sin necesidad de entrar al formulario completo.
     */
    public function toggleActivo(Examen $examen): RedirectResponse
    {
        $examen->update(['activo' => !$examen->activo]);

        return back()->with('success',
            $examen->activo ? 'Examen activado.' : 'Examen desactivado.'
        );
    }
}