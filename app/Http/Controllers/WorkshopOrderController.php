<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:workshop_orders.view')->only(['index', 'show']);
        $this->middleware('can:workshop_orders.create')->only(['create', 'store']);
        $this->middleware('can:workshop_orders.edit')->only(['edit', 'update']);
        $this->middleware('can:workshop_orders.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = WorkshopOrder::query()
            ->with([
                'vehicle:id,plate,brand,model,physical_status',
                'provider:id,name,type',
                'createdBy:id,name',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery
                            ->where('plate', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('provider', function ($providerQuery) use ($search) {
                        $providerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $orders = $query
            ->latest('created_at')
            ->paginate(10)
            ->through(fn(WorkshopOrder $order) => $this->serializeOrder($order))
            ->appends($request->query());

        return Inertia::render('WorkshopOrders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'stats' => [
                'total' => WorkshopOrder::count(),
                'active' => WorkshopOrder::query()->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])->count(),
                'pending' => WorkshopOrder::query()->where('status', 'pendiente')->count(),
                'finished' => WorkshopOrder::query()->where('status', 'finalizado')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('WorkshopOrders/Create', [
            'vehicles' => $this->vehicleOptions(),
            'providers' => $this->providerOptions(),
            'users' => $this->userOptions(),
            'orderTypes' => $this->orderTypes(),
            'orderStatuses' => $this->orderStatuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        WorkshopOrder::create($validated);

        return redirect()
            ->route('workshop.orders.index')
            ->with('success', 'Orden de taller creada correctamente.');
    }

    public function show(WorkshopOrder $order): Response
    {
        $order->load([
            'vehicle:id,plate,brand,model,version,physical_status,commercial_status',
            'provider:id,name,type',
            'createdBy:id,name',
            'checklistItems',
        ]);

        return Inertia::render('WorkshopOrders/Show', [
            'order' => [
                ...$this->serializeOrder($order),
                'checklist_items' => $order->checklistItems->map(fn($item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'sort_order' => $item->sort_order,
                    'passed' => $item->passed,
                    'has_incident' => $item->has_incident,
                    'notes' => $item->notes,
                    'checked_at' => optional($item->checked_at)?->format('Y-m-d H:i'),
                ])->values(),
            ],
        ]);
    }

    public function edit(WorkshopOrder $order): Response
    {
        return Inertia::render('WorkshopOrders/Edit', [
            'order' => [
                'id' => $order->id,
                'vehicle_id' => $order->vehicle_id,
                'provider_id' => $order->provider_id,
                'type' => $order->type,
                'status' => $order->status,
                'entry_at' => optional($order->entry_at)?->format('Y-m-d\TH:i'),
                'exit_at' => optional($order->exit_at)?->format('Y-m-d\TH:i'),
                'exit_photo_url' => $order->exit_photo_url,
                'exit_photo_disk' => $order->exit_photo_disk,
                'created_by_user_id' => $order->created_by_user_id,
                'notes' => $order->notes,
            ],
            'vehicles' => $this->vehicleOptions(),
            'providers' => $this->providerOptions(),
            'users' => $this->userOptions(),
            'orderTypes' => $this->orderTypes(),
            'orderStatuses' => $this->orderStatuses(),
        ]);
    }

    public function update(Request $request, WorkshopOrder $order): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $order->update($validated);

        return redirect()
            ->route('workshop.orders.index')
            ->with('success', 'Orden de taller actualizada correctamente.');
    }

    public function destroy(WorkshopOrder $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('workshop.orders.index')
            ->with('success', 'Orden de taller eliminada correctamente.');
    }

    private function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'type' => ['required', Rule::in(array_column($this->orderTypes(), 'value'))],
            'status' => ['required', Rule::in(array_column($this->orderStatuses(), 'value'))],
            'entry_at' => ['nullable', 'date'],
            'exit_at' => ['nullable', 'date', 'after_or_equal:entry_at'],
            'exit_photo_url' => ['nullable', 'string', 'max:2048'],
            'exit_photo_disk' => ['nullable', 'string', 'max:50'],
            'created_by_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function serializeOrder(WorkshopOrder $order): array
    {
        return [
            'id' => $order->id,
            'type' => $order->type,
            'status' => $order->status,
            'entry_at' => optional($order->entry_at)?->format('Y-m-d H:i'),
            'exit_at' => optional($order->exit_at)?->format('Y-m-d H:i'),
            'notes' => $order->notes,
            'exit_photo_url' => $order->exit_photo_url,
            'exit_photo_disk' => $order->exit_photo_disk,
            'days_in_workshop' => $order->days_in_workshop,
            'vehicle' => $order->vehicle ? [
                'id' => $order->vehicle->id,
                'plate' => $order->vehicle->plate,
                'brand' => $order->vehicle->brand,
                'model' => $order->vehicle->model,
                'physical_status' => $order->vehicle->physical_status,
                'commercial_status' => $order->vehicle->commercial_status ?? null,
                'version' => $order->vehicle->version ?? null,
            ] : null,
            'provider' => $order->provider ? [
                'id' => $order->provider->id,
                'name' => $order->provider->name,
                'type' => $order->provider->type,
            ] : null,
            'created_by' => $order->createdBy ? [
                'id' => $order->createdBy->id,
                'name' => $order->createdBy->name,
            ] : null,
            'checklist_items_count' => $order->relationLoaded('checklistItems')
                ? $order->checklistItems->count()
                : $order->checklistItems()->count(),
        ];
    }

    private function vehicleOptions(): array
    {
        return Vehicle::query()
            ->select('id', 'plate', 'brand', 'model')
            ->orderBy('plate')
            ->get()
            ->map(fn(Vehicle $vehicle) => [
                'id' => $vehicle->id,
                'label' => "{$vehicle->plate} - {$vehicle->brand} {$vehicle->model}",
            ])
            ->all();
    }

    private function providerOptions(): array
    {
        return Provider::query()
            ->select('id', 'name', 'type')
            ->whereIn('type', ['chapa', 'dab', 'limpieza', 'recepcion'])
            ->orderBy('name')
            ->get()
            ->map(fn(Provider $provider) => [
                'id' => $provider->id,
                'label' => "{$provider->name} ({$provider->type})",
            ])
            ->all();
    }

    private function userOptions(): array
    {
        return User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->id,
                'label' => $user->name,
            ])
            ->all();
    }

    private function orderTypes(): array
    {
        return [
            ['value' => 'chapa', 'label' => 'Chapa'],
            ['value' => 'dab', 'label' => 'DAB'],
            ['value' => 'limpieza', 'label' => 'Limpieza'],
        ];
    }

    private function orderStatuses(): array
    {
        return [
            ['value' => 'pendiente', 'label' => 'Pendiente'],
            ['value' => 'en_proceso', 'label' => 'En proceso'],
            ['value' => 'incidencia', 'label' => 'Incidencia'],
            ['value' => 'finalizado', 'label' => 'Finalizado'],
        ];
    }
}
