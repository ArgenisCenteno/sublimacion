<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PacienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): Response
    {
        $query = Paciente::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $upper = Str::upper($search);

            $query->where(function ($q) use ($search, $upper) {
                $q->where('cedula', 'like', "%{$upper}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('es_menor')) {
            $esMenor = filter_var($request->es_menor, FILTER_VALIDATE_BOOLEAN);
            $query->whereRaw(
                $esMenor
                ? 'fecha_nacimiento > DATE_SUB(CURDATE(), INTERVAL 18 YEAR)'
                : 'fecha_nacimiento <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR)'
            );
        }

        $pacientes = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Recepcion/Pacientes/Index', [
            'pacientes' => $pacientes,
            'filters' => $request->only(['search', 'es_menor']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Recepcion/Pacientes/Create');
    }

    public function store(StorePacienteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $fechaNacimiento = Carbon::parse($data['fecha_nacimiento']);
        $edad = $fechaNacimiento->age;

        $data['es_menor'] = $edad < 18 ? 1 : 0;

        $paciente = Paciente::create($data);

        return redirect()
            ->route('ordenes.create', ['paciente_id' => $paciente->id])
            ->with('success', "Paciente {$paciente->nombre_completo} registrado. Ahora crea la orden.");
    }

    public function show(Paciente $paciente): Response
    {
        $paciente->load(['ordenes.examenes']);

        return Inertia::render('Recepcion/Pacientes/Show', [
            'paciente' => $this->serializePaciente($paciente),
        ]);
    }

    public function edit(Paciente $paciente): Response
    {
         
        return Inertia::render('Recepcion/Pacientes/Edit', [
            'paciente' => $paciente,
        ]);
    }

    public function update(UpdatePacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->update($request->validated());

        return redirect()
            ->route('pacientes.show', $paciente)
            ->with('success', 'Datos del paciente actualizados.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $paciente->delete();

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente eliminado.');
    }

    /**
     * Búsqueda rápida para el autocompletado al crear una orden.
     * Devuelve JSON, no una página Inertia.
     */
    public function buscar(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->string('q'));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $upper = Str::upper($search);

        $pacientes = Paciente::query()
            ->where(function ($q) use ($search, $upper) {
                $q->where('cedula', 'like', "%{$upper}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            })
            ->orderBy('apellidos')
            ->limit(10)
            ->get(['id', 'cedula', 'nombres', 'apellidos', 'fecha_nacimiento', 'telefono']);

        return response()->json(
            $pacientes->map(fn(Paciente $p) => $this->serializePacienteResumen($p))
        );
    }

    private function serializePaciente(Paciente $paciente): array
    {
        $data = $paciente->toArray();

        $data['edad'] = $paciente->edad;
        $data['es_menor'] = $paciente->es_menor;
        $data['nombre_completo'] = $paciente->nombre_completo;

        return $data;
    }

    /**
     * Versión reducida para el autocompletado y la búsqueda rápida.
     */
    private function serializePacienteResumen(Paciente $paciente): array
    {
        return [
            'id' => $paciente->id,
            'cedula' => $paciente->cedula,
            'nombre_completo' => $paciente->nombre_completo,
            'edad' => $paciente->edad,
            'es_menor' => $paciente->es_menor,
            'telefono' => $paciente->telefono,
        ];
    }
}