<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\Alerts\AlertCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:alerts.view')->only(['index']);
        $this->middleware('can:alerts.run_action')->only(['runAction']);
        $this->middleware('can:alerts.resolve')->only(['resolve']);
        $this->middleware('can:alerts.dismiss')->only(['dismiss']);
    }
    public function index(Request $request, AlertCenterService $alertCenterService): Response
    {
        $alertCenterService->sync();

        $query = Alert::query()
            ->with([
                'vehicle:id,plate,brand,model,physical_status,commercial_status',
                'resolvedBy:id,name',
            ])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        } else {
            $query->where('status', 'pendiente');
        }

        $alerts = $query->get()->map(function (Alert $alert) use ($alertCenterService) {
            $config = $alertCenterService->configForType($alert->type);

            return [
                'id' => $alert->id,
                'type' => $alert->type,
                'priority' => $alert->priority,
                'status' => $alert->status,
                'segment' => $config['segment'],
                'role_target' => $config['role_target'],
                'title' => $config['title'],
                'action_label' => $config['action_label'],
                'payload' => $alert->payload ?? [],
                'created_at' => optional($alert->created_at)->format('Y-m-d H:i'),
                'resolved_at' => optional($alert->resolved_at)->format('Y-m-d H:i'),
                'resolution_notes' => $alert->resolution_notes,
                'resolved_by' => $alert->resolvedBy?->name,
                'vehicle' => $alert->vehicle ? [
                    'id' => $alert->vehicle->id,
                    'plate' => $alert->vehicle->plate,
                    'brand' => $alert->vehicle->brand,
                    'model' => $alert->vehicle->model,
                    'physical_status' => $alert->vehicle->physical_status,
                    'commercial_status' => $alert->vehicle->commercial_status,
                ] : null,
            ];
        });

        if ($request->filled('role_target')) {
            $roleTarget = $request->string('role_target')->toString();
            $alerts = $alerts->where('role_target', $roleTarget);
        }

        $alerts = $alerts->values();

        return Inertia::render('Alerts/Index', [
            'alerts' => $alerts,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'role_target' => $request->string('role_target')->toString(),
            ],
            'stats' => [
                'critical' => $alerts->where('segment', 'critica')->count(),
                'follow_up' => $alerts->where('segment', 'seguimiento')->count(),
                'operational' => $alerts->where('segment', 'operativa')->count(),
                'resolved_today' => Alert::query()
                    ->whereIn('status', ['resuelta', 'descartada'])
                    ->whereDate('resolved_at', today())
                    ->count(),
            ],
        ]);
    }

    public function runAction(Alert $alert, Request $request, AlertCenterService $alertCenterService): RedirectResponse
    {
        $message = $alertCenterService->executePrimaryAction($alert, $request->user());

        return redirect()
            ->route('alerts')
            ->with('success', $message);
    }

    public function resolve(Alert $alert, Request $request, AlertCenterService $alertCenterService): RedirectResponse
    {
        $message = $alertCenterService->resolveAlert($alert, $request->user(), 'Resuelta manualmente desde el centro de alertas.');

        return redirect()
            ->route('alerts')
            ->with('success', $message);
    }

    public function dismiss(Alert $alert, Request $request, AlertCenterService $alertCenterService): RedirectResponse
    {
        $message = $alertCenterService->dismissAlert($alert, $request->user(), 'Descartada manualmente desde el centro de alertas.');

        return redirect()
            ->route('alerts')
            ->with('success', $message);
    }
}
