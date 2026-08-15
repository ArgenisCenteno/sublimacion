<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:permissions.view')->only(['index', 'show']);
        $this->middleware('can:permissions.create')->only(['create', 'store']);
        $this->middleware('can:permissions.edit')->only(['edit', 'update']);
        $this->middleware('can:permissions.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = Permission::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $permissions = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
            'filters'     => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Permissions/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permiso creado correctamente.');
    }

    public function show(Permission $permission): Response
    {
        return Inertia::render('Permissions/Show', [
            'permission' => $permission,
        ]);
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('permissions')->ignore($permission->id),
            ],
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permiso eliminado correctamente.');
    }
}
