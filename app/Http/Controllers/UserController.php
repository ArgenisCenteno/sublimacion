<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Password;
use Spatie\Permission\Models\Role;
use Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:users.view')->only(['index', 'show']);
        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.edit')->only(['edit', 'update']);
        $this->middleware('can:users.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = User::with(['roles', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'allRoles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_id' => 'nullable|exists:companies,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'can_view_all_companies' => 'boolean',
            'active' => 'boolean',
        ]);

        $user = User::create($validated);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user): Response
    {
        $user->load(['roles', 'company']);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['roles', 'company']);

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'allRoles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'company_id' => 'nullable|exists:companies,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'can_view_all_companies' => 'boolean',
            'active' => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function profile(): Response
    {
        $user = auth()->user()->load(['roles', 'company']);

        return Inertia::render('Users/Profile', [
            'user' => $user,
        ]);
    }
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back();
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('La contraseña actual no es correcta.'),
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back();
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Eliminar avatar anterior si existe
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar_url' => $path]);

        return back();
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->update(['avatar_url' => null]);

        return back();
    }
}
