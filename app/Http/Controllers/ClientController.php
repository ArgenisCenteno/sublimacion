<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:clients.view')->only(['index', 'show']);
        $this->middleware('can:clients.create')->only(['create', 'store']);
        $this->middleware('can:clients.edit')->only(['edit', 'update']);
        $this->middleware('can:clients.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nif', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $clients = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['country'] = $validated['country'] ?? 'ES';

        if ($validated['type'] === 'juridica') {
            $validated['name'] = $validated['company_name'];
        }

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client): Response
    {
        $client->load(['saleContracts', 'rentingContracts']);

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate($this->rules($client));

        if ($validated['type'] === 'juridica') {
            $validated['name'] = $validated['company_name'];
        }

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    private function rules(?Client $client = null): array
    {
        return [
            'type' => ['required', Rule::in(['fisica', 'juridica'])],
            'name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'nullable',
                'string',
                'max:50',
                $client
                ? Rule::unique('clients')->ignore($client->id)
                : Rule::unique('clients', 'nif'),
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
