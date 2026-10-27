<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Lab404\Impersonate\Services\ImpersonateManager;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:users.impersonate')->only(['take']);
    }

    public function take(Request $request, User $user): Response
    {
        $admin = $request->user();

        abort_unless($admin->canImpersonate() && $user->canBeImpersonated(), 403);

        $admin->impersonate($user);

        activity()
            ->performedOn($user)
            ->causedBy($admin)
            ->useLog('impersonación')
            ->log("Inició impersonación de {$user->name}");

        return Inertia::location(route('dashboard'));
    }

    public function leave(Request $request, ImpersonateManager $manager): Response
    {
        abort_unless($request->user()->isImpersonated(), 403);

        $impersonator = $manager->getImpersonator();
        $impersonated = $request->user();

        $request->user()->leaveImpersonation();

        activity()
            ->performedOn($impersonated)
            ->causedBy($impersonator)
            ->useLog('impersonación')
            ->log("Terminó impersonación de {$impersonated->name}");

        return Inertia::location(route('users.index'));
    }
}
