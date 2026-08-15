<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:providers.view')->only(['index', 'show']);
        $this->middleware('can:providers.create')->only(['create', 'store']);
        $this->middleware('can:providers.edit')->only(['edit', 'update']);
        $this->middleware('can:providers.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = Provider::query()->with('company');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $providers = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Providers/Index', [
            'providers' => $providers,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'active' => $request->input('active'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Providers/Create', [
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['active'] = $validated['active'] ?? true;
        $validated['pin'] = Hash::make($validated['pin']);

        Provider::create($validated);

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function show(Provider $provider): Response
    {
        $provider->load(['company', 'vehicles']);

        return Inertia::render('Providers/Show', [
            'provider' => $provider,
        ]);
    }

    public function edit(Provider $provider): Response
    {
        return Inertia::render('Providers/Edit', [
            'provider' => $provider,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validate($this->rules($provider));

        // Solo re-hashear el PIN si se ha enviado uno nuevo
        if (!empty($validated['pin'])) {
            $validated['pin'] = Hash::make($validated['pin']);
        } else {
            unset($validated['pin']);
        }

        $provider->update($validated);

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $provider->delete();

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    private function rules(?Provider $provider = null): array
    {
        return [
            'company_id' => $provider ? 'nullable|exists:companies,id' : 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['chapa', 'dab', 'limpieza', 'recepcion', 'proveedor_vehiculos'])],
            'pin' => $provider ? 'nullable|string|min:4|max:255' : 'required|string|min:4|max:255',
            'pin_prefix' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'geofence_radius_meters' => 'nullable|integer|min:0',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => [
                'nullable',
                'string',
                'max:50',
                $provider
                ? Rule::unique('providers')->ignore($provider->id)
                : Rule::unique('providers', 'tax_id'),
            ],
            'active' => 'boolean',
        ];
    }
}
