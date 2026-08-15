<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\WorkshopChecklistItem;
use App\Models\WorkshopOrder;
use App\Services\Operations\ChapaFinishService;
use App\Services\Operations\ChapaIncidentService;
use App\Services\Operations\ChapaReceptionService;
use App\Services\Operations\DabChecklistService;
use App\Services\Operations\DabFinishService;
use App\Services\Operations\DabReceptionService;
use App\Services\Operations\LimpiezaFinishService;
use App\Services\Operations\LimpiezaReceptionService;
use App\Services\Operations\PlateRecognitionService;
use App\Services\Operations\RecepcionReleaseService;
use App\Services\Operations\VehicleDamageMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OperationsAccessController extends Controller
{
    private const SESSION_CONTEXT_KEY = 'ops.provider_id';

    // ─── Vistas públicas ─────────────────────────────────────────────────────

    public function index(): Response
    {
        return Inertia::render('Ops/Access');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        $provider = $this->resolveProviderFromPin($validated['pin']);

        if (! $provider) {
            throw ValidationException::withMessages([
                'pin' => 'El PIN no es valido o el taller no esta disponible.',
            ]);
        }

        $request->session()->put(self::SESSION_CONTEXT_KEY, $provider->id);

        return redirect()->route($this->routeNameForProvider($provider));
    }

    // ─── CHAPA ───────────────────────────────────────────────────────────────

    public function chapa(Request $request): Response|RedirectResponse
    {
        return $this->resolveOperationView($request, 'chapa', 'Ops/Chapa');
    }

    public function analyzeChapaPlate(Request $request, PlateRecognitionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'chapa');

        ['plate' => $plate, 'confidence' => $confidence, 'vehicle' => $vehicle] =
            $this->recognizeAndFindVehicle($request, $service);

        $receivableOrder = $this->findReceivableOrder($vehicle, 'chapa');
        $activeOrder = $this->findActiveOrder($vehicle, 'chapa');

        if (! $receivableOrder) {
            return response()->json([
                'plate' => $plate,
                'confidence' => $confidence,
                'vehicle' => $this->serializeVehicle($vehicle),
                'damages' => $this->serializeDamages($vehicle),
                'workflow_stage' => 'unavailable',
                'stage_available' => false,
                'workshop_order' => null,
                'message' => $this->missingWorkshopOrderMessage($vehicle, 'chapa'),
            ]);
        }

        return response()->json([
            'plate'          => $plate,
            'confidence'     => $confidence,
            'vehicle'        => $this->serializeVehicle($vehicle),
            'damages'        => $this->serializeDamages($vehicle),
            'workflow_stage' => $activeOrder ? 'work_in_progress' : 'reception',
            'stage_available' => true,
            'workshop_order' => $this->serializeActiveOrder($activeOrder),
        ]);
    }

    public function confirmChapaReception(Request $request, ChapaReceptionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'chapa');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'plate'           => ['nullable', 'string', 'max:20'],
            'reception_photo' => ['required', 'image', 'max:10240'],
            'latitude'        => ['required', 'numeric'],
            'longitude'       => ['required', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $this->assertPlateMatch($validated['plate'] ?? null, $vehicle->plate);

        return response()->json(
            $service->confirm($provider, $vehicle, $validated, $validated['reception_photo'], $request)
        );
    }

    public function reportChapaIncident(Request $request, ChapaIncidentService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'chapa');

        $validated = $request->validate([
            'vehicle_id'  => ['required', 'exists:vehicles,id'],
            'description' => ['required', 'string', 'max:255'],
            'severity'    => ['required', 'in:leve,moderado,grave'],
            'notes'       => ['nullable', 'string', 'max:1000'],
            'photos'      => ['nullable', 'array'],
            'photos.*'    => ['image', 'max:10240'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);

        return response()->json($service->report($provider, $vehicle, $validated));
    }

    public function reportChapaMappedDamage(Request $request, VehicleDamageMapService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'chapa');
        $validated = $this->validateDamagePayload($request);

        return response()->json(
            $service->report(
                $provider,
                Vehicle::query()->findOrFail($validated['vehicle_id']),
                $validated,
                'taller_chapa'
            )
        );
    }

    public function finishChapaRepair(Request $request, ChapaFinishService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'chapa');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'photos'          => ['required', 'array', 'min:1'],
            'photos.*'        => ['image', 'max:10240'],
            'latitude'        => ['nullable', 'numeric'],
            'longitude'       => ['nullable', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);

        return response()->json($service->finish($provider, $vehicle, $validated, $request));
    }

    // ─── DAB ─────────────────────────────────────────────────────────────────

    public function dab(Request $request): Response|RedirectResponse
    {
        return $this->resolveOperationView($request, 'dab', 'Ops/Dab');
    }

    public function analyzeDabPlate(Request $request, PlateRecognitionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'dab');

        ['plate' => $plate, 'confidence' => $confidence, 'vehicle' => $vehicle] =
            $this->recognizeAndFindVehicle($request, $service);

        $receivableOrder = $this->findReceivableOrder($vehicle, 'dab');
        $activeOrder = $this->findActiveOrder($vehicle, 'dab');

        if (! $receivableOrder) {
            return response()->json([
                'plate' => $plate,
                'confidence' => $confidence,
                'vehicle' => $this->serializeVehicle($vehicle),
                'damages' => $this->serializeDamages($vehicle),
                'workflow_stage' => 'unavailable',
                'stage_available' => false,
                'workshop_order' => null,
                'checklist_items' => [],
                'message' => $this->missingWorkshopOrderMessage($vehicle, 'dab'),
            ]);
        }

        return response()->json([
            'plate'           => $plate,
            'confidence'      => $confidence,
            'vehicle'         => $this->serializeVehicle($vehicle),
            'damages'         => $this->serializeDamages($vehicle),
            'workflow_stage'  => $activeOrder ? 'work_in_progress' : 'reception',
            'stage_available' => true,
            'workshop_order'  => $this->serializeActiveOrder($activeOrder),
            'checklist_items' => $activeOrder ? $this->serializeChecklistItems($activeOrder) : [],
        ]);
    }

    public function confirmDabReception(Request $request, DabReceptionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'dab');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'plate'           => ['nullable', 'string', 'max:20'],
            'latitude'        => ['required', 'numeric'],
            'longitude'       => ['required', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $this->assertPlateMatch($validated['plate'] ?? null, $vehicle->plate);

        return response()->json($service->confirm($provider, $vehicle, $validated, $request));
    }

    public function updateDabChecklistItem(Request $request, DabChecklistService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'dab');

        $validated = $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'checklist_item_id' => ['required', 'exists:workshop_checklist_items,id'],
            'passed'            => ['required', 'boolean'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);

        return response()->json($service->update($provider, $vehicle, $validated));
    }

    public function finishDabReview(Request $request, DabFinishService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'dab');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'latitude'        => ['nullable', 'numeric'],
            'longitude'       => ['nullable', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);

        return response()->json($service->finish($provider, $vehicle, $validated, $request));
    }

    public function reportDabMappedDamage(Request $request, VehicleDamageMapService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'dab');
        $validated = $this->validateDamagePayload($request);

        return response()->json(
            $service->report(
                $provider,
                Vehicle::query()->findOrFail($validated['vehicle_id']),
                $validated,
                'taller_dab'
            )
        );
    }

    // ─── LIMPIEZA ────────────────────────────────────────────────────────────

    public function limpieza(Request $request): Response|RedirectResponse
    {
        return $this->resolveOperationView($request, 'limpieza', 'Ops/Limpieza');
    }

    public function analyzeLimpiezaPlate(Request $request, PlateRecognitionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'limpieza');

        ['plate' => $plate, 'confidence' => $confidence, 'vehicle' => $vehicle] =
            $this->recognizeAndFindVehicle($request, $service);

        $receivableOrder = $this->findReceivableOrder($vehicle, 'limpieza');
        $activeOrder = $this->findActiveOrder($vehicle, 'limpieza');

        if (! $receivableOrder) {
            return response()->json([
                'plate' => $plate,
                'confidence' => $confidence,
                'vehicle' => $this->serializeVehicle($vehicle),
                'damages' => $this->serializeDamages($vehicle),
                'workflow_stage' => 'unavailable',
                'stage_available' => false,
                'workshop_order' => null,
                'message' => $this->missingWorkshopOrderMessage($vehicle, 'limpieza'),
            ]);
        }

        return response()->json([
            'plate'          => $plate,
            'confidence'     => $confidence,
            'vehicle'        => $this->serializeVehicle($vehicle),
            'damages'        => $this->serializeDamages($vehicle),
            'workflow_stage' => $activeOrder ? 'work_in_progress' : 'reception',
            'stage_available' => true,
            'workshop_order' => $this->serializeActiveOrder($activeOrder),
        ]);
    }

    public function confirmLimpiezaReception(Request $request, LimpiezaReceptionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'limpieza');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'plate'           => ['nullable', 'string', 'max:20'],
            'latitude'        => ['required', 'numeric'],
            'longitude'       => ['required', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $this->assertPlateMatch($validated['plate'] ?? null, $vehicle->plate);

        return response()->json($service->confirm($provider, $vehicle, $validated, $request));
    }

    public function finishLimpiezaCleaning(Request $request, LimpiezaFinishService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'limpieza');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'photos'          => ['required', 'array', 'min:1'],
            'photos.*'        => ['image', 'max:10240'],
            'latitude'        => ['nullable', 'numeric'],
            'longitude'       => ['nullable', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);

        return response()->json($service->finish($provider, $vehicle, $validated, $request));
    }

    public function reportLimpiezaMappedDamage(Request $request, VehicleDamageMapService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'limpieza');
        $validated = $this->validateDamagePayload($request);

        return response()->json(
            $service->report(
                $provider,
                Vehicle::query()->findOrFail($validated['vehicle_id']),
                $validated,
                'taller_limpieza'
            )
        );
    }

    // ─── RECEPCIÓN CAMPA ─────────────────────────────────────────────────────

    public function recepcion(Request $request): Response|RedirectResponse
    {
        return $this->resolveOperationView($request, 'recepcion', 'Ops/Recepcion');
    }

    public function analyzeRecepcionPlate(Request $request, PlateRecognitionService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'recepcion');

        [
            'plate' => $plate,
            'confidence' => $confidence,
            'vehicle' => $vehicle,
            'suggested_vehicle' => $suggestedVehicle,
        ] = $this->recognizeAndFindVehicle($request, $service, false);

        if (! $vehicle) {
            return response()->json([
                'found'             => false,
                'plate'             => $plate,
                'confidence'        => $confidence,
                'vehicle'           => null,
                'damages'           => [],
                'suggested_vehicle' => $suggestedVehicle,
                'workflow_plan'     => null,
                'workflow_stage'    => 'initial_reception',
                'message'           => 'No encontramos este vehiculo. Si acaba de llegar, puedes crearlo desde aqui.',
            ]);
        }

        $workflowStage = match ($vehicle->physical_status) {
            'en_transito'        => 'initial_reception',
            'pendiente_recogida' => 'final_reception',
            default              => 'unsupported',
        };

        return response()->json([
            'found'         => true,
            'plate'         => $plate,
            'confidence'    => $confidence,
            'vehicle'       => $this->serializeVehicle($vehicle),
            'damages'       => $this->serializeDamages($vehicle),
            'workflow_plan' => $vehicle->workflowPlan ? [
                'chapa'    => $vehicle->workflowPlan->requires_chapa,
                'dab'      => $vehicle->workflowPlan->requires_dab,
                'limpieza' => $vehicle->workflowPlan->requires_limpieza,
            ] : null,
            'workflow_stage' => $workflowStage,
            'message'        => match ($workflowStage) {
                'initial_reception' => 'Todo listo para recibir este vehiculo en campa.',
                'final_reception'   => 'Todo listo para marcar este vehiculo como disponible.',
                default             => 'Ahora mismo este vehiculo no necesita ninguna gestion en campa.',
            },
        ]);
    }

    public function releaseRecepcionVehicle(Request $request, RecepcionReleaseService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'recepcion');

        $validated = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'plate'           => ['nullable', 'string', 'max:20'],
            'latitude'        => ['required', 'numeric'],
            'longitude'       => ['required', 'numeric'],
            'accuracy_meters' => ['nullable', 'numeric'],
            'workflow_plan'   => ['nullable', 'array'],
            'workflow_plan.*' => ['string', 'in:chapa,dab,limpieza', 'distinct'],
            'photos'          => ['nullable', 'array'],
            'photos.*'        => ['image', 'max:10240'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $this->assertPlateMatch($validated['plate'] ?? null, $vehicle->plate);

        return response()->json($service->release($provider, $vehicle, $validated, $request));
    }

    public function reportRecepcionMappedDamage(Request $request, VehicleDamageMapService $service): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'recepcion');
        $validated = $this->validateDamagePayload($request);

        return response()->json(
            $service->report(
                $provider,
                Vehicle::query()->findOrFail($validated['vehicle_id']),
                $validated,
                'recepcion'
            )
        );
    }

    public function createRecepcionVehicle(Request $request): JsonResponse
    {
        $provider = $this->providerFromSession($request);
        $this->ensureProviderType($provider, 'recepcion');

        $validated = $request->validate([
            'plate'   => ['required', 'string', 'max:20'],
            'brand'   => ['required', 'string', 'max:100'],
            'model'   => ['required', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:150'],
            'color'   => ['nullable', 'string', 'max:60'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $normalizedPlate = $this->normalizePlate($validated['plate']);

        if ($normalizedPlate === '') {
            throw ValidationException::withMessages([
                'plate' => 'Introduce una matricula valida para crear el vehiculo.',
            ]);
        }

        if (Vehicle::query()->where('plate', $normalizedPlate)->exists()) {
            throw ValidationException::withMessages([
                'plate' => 'Ya hay un vehiculo registrado con esa matricula.',
            ]);
        }

        $vehicle = Vehicle::query()->create([
            'company_id' => $provider->company_id,
            'provider_id' => null,
            'plate' => $normalizedPlate,
            'vin' => null,
            'brand' => trim($validated['brand']),
            'model' => trim($validated['model']),
            'version' => $this->nullableTrimmed($validated['version'] ?? null),
            'color' => $this->nullableTrimmed($validated['color'] ?? null),
            'registration_date' => null,
            'mileage_km' => 0,
            'raw_location' => 'Recepcion Campa',
            'raw_physical_status' => 'creado_desde_recepcion',
            'salesperson_name' => null,
            'physical_status' => 'en_transito',
            'commercial_status' => 'no_publicable',
            'taxable_base' => null,
            'pvi' => null,
            'price_financed' => null,
            'price_cash' => null,
            'num_keys' => 0,
            'has_ficha_tecnica' => false,
            'has_permiso_circulacion' => false,
            'insurance_active' => false,
            'insurance_activated_at' => null,
            'exported_to_motorflash' => false,
            'last_export_at' => null,
            'latitude' => null,
            'longitude' => null,
            'location_updated_at' => null,
            'notes' => $this->nullableTrimmed($validated['notes'] ?? null),
        ]);

        return response()->json([
            'message' => 'Vehiculo creado. Ya puedes continuar con la recepcion en campa.',
            'created' => true,
            'plate' => $vehicle->plate,
            'confidence' => 1,
            'vehicle' => $this->serializeVehicle($vehicle),
            'damages' => [],
            'workflow_plan' => null,
            'workflow_stage' => 'initial_reception',
        ], 201);
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Reconoce la matrícula en la imagen y busca el vehículo en BD.
     * Usado por los 4 métodos analyzePlate* — elimina duplicación DRY.
     *
     * @return array{
     *     plate: string,
     *     confidence: float|int,
     *     vehicle: Vehicle|null,
     *     suggested_vehicle: array<string, mixed>|null
     * }
     */
    private function recognizeAndFindVehicle(
        Request $request,
        PlateRecognitionService $service,
        bool $abortWhenNotFound = true
    ): array
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $ocr             = $service->recognizePlate($validated['image']);
        $normalizedPlate = $this->normalizePlate($ocr['plate'] ?? '');

        if ($normalizedPlate === '') {
            throw ValidationException::withMessages([
                'image' => 'No pudimos leer bien la matricula en la foto.',
            ]);
        }

        $vehicle = Vehicle::query()
            ->select(['id', 'plate', 'brand', 'model', 'version', 'color', 'physical_status', 'commercial_status'])
            ->get()
            ->first(fn (Vehicle $item) => $this->normalizePlate($item->plate) === $normalizedPlate);

        if (! $vehicle) {
            $suggestedVehicle = $service->suggestVehicleData($validated['image'], $normalizedPlate);

            if ($abortWhenNotFound) {
                abort(response()->json([
                    'plate'             => $normalizedPlate,
                    'confidence'        => $ocr['confidence'] ?? 0,
                    'vehicle'           => null,
                    'suggested_vehicle' => $suggestedVehicle,
                    'message'           => 'No encontramos este vehiculo.',
                ], 404));
            }

            return [
                'plate'             => $normalizedPlate,
                'confidence'        => $ocr['confidence'] ?? 0,
                'vehicle'           => null,
                'suggested_vehicle' => $suggestedVehicle,
            ];
        }

        return [
            'plate'             => $normalizedPlate,
            'confidence'        => $ocr['confidence'] ?? 0,
            'vehicle'           => $vehicle,
            'suggested_vehicle' => null,
        ];
    }

    /** Busca la orden activa de un tipo dado para un vehículo. */
    private function findActiveOrder(Vehicle $vehicle, string $type): ?WorkshopOrder
    {
        return WorkshopOrder::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', $type)
            ->whereIn('status', ['en_proceso', 'incidencia'])
            ->latest('id')
            ->first();
    }

    private function findReceivableOrder(Vehicle $vehicle, string $type): ?WorkshopOrder
    {
        return WorkshopOrder::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', $type)
            ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
            ->latest('id')
            ->first();
    }

    private function missingWorkshopOrderMessage(Vehicle $vehicle, string $type): string
    {
        $plan = $vehicle->workflowPlan;
        $stageEnabled = match ($type) {
            'chapa' => $plan?->requires_chapa ?? false,
            'dab' => $plan?->requires_dab ?? false,
            'limpieza' => $plan?->requires_limpieza ?? false,
            default => false,
        };

        if (! $stageEnabled) {
            return 'Esta etapa no forma parte del recorrido actual de este vehiculo.';
        }

        $currentOrder = WorkshopOrder::query()
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
            ->latest('id')
            ->first();

        if ($currentOrder && $currentOrder->type !== $type) {
            return sprintf(
                'Todavia no hay una orden de %s preparada para este vehiculo. Ahora mismo sigue en %s.',
                $this->workshopTypeLabel($type),
                $this->workshopTypeLabel($currentOrder->type)
            );
        }

        return sprintf(
            'Todavia no hay una orden de %s preparada para este vehiculo. Intenta de nuevo cuando la etapa anterior haya terminado.',
            $this->workshopTypeLabel($type)
        );
    }

    private function workshopTypeLabel(string $type): string
    {
        return match ($type) {
            'chapa' => 'chapa',
            'dab' => 'DAB',
            'limpieza' => 'limpieza',
            default => str_replace('_', ' ', $type),
        };
    }

    /** Serializa los datos públicos de un vehículo para las respuestas JSON. */
    private function serializeVehicle(Vehicle $vehicle): array
    {
        return [
            'id'                => $vehicle->id,
            'plate'             => $vehicle->plate,
            'brand'             => $vehicle->brand,
            'model'             => $vehicle->model,
            'version'           => $vehicle->version,
            'color'             => $vehicle->color,
            'physical_status'   => $vehicle->physical_status,
            'commercial_status' => $vehicle->commercial_status,
        ];
    }

    private function serializeDamages(Vehicle $vehicle): array
    {
        return app(VehicleDamageMapService::class)->listForVehicle($vehicle);
    }

    /** Serializa una orden activa o devuelve null. */
    private function serializeActiveOrder(?WorkshopOrder $order): ?array
    {
        if (! $order) {
            return null;
        }

        return [
            'id'       => $order->id,
            'status'   => $order->status,
            'entry_at' => optional($order->entry_at)->toIso8601String(),
            'exit_at'  => optional($order->exit_at)->toIso8601String(),
        ];
    }

    /** Valida que la matrícula detectada coincida con la del vehículo. */
    private function assertPlateMatch(?string $detectedPlate, string $vehiclePlate): void
    {
        if (! empty($detectedPlate) && $this->normalizePlate($detectedPlate) !== $this->normalizePlate($vehiclePlate)) {
            throw ValidationException::withMessages([
                'plate' => 'La matricula de la foto no coincide con el vehiculo seleccionado.',
            ]);
        }
    }

    private function resolveOperationView(Request $request, string $expectedType, string $viewName): Response|RedirectResponse
    {
        $provider = $this->providerFromSession($request);

        if (! $provider) {
            return redirect()->route('ops.index');
        }

        if ($provider->type !== $expectedType) {
            return redirect()->route($this->routeNameForProvider($provider));
        }

        return Inertia::render($viewName, [
            'provider' => [
                'name' => $provider->name,
                'type' => $provider->type,
            ],
        ]);
    }

    private function providerFromSession(Request $request): ?Provider
    {
        $providerId = $request->session()->get(self::SESSION_CONTEXT_KEY);

        if (! $providerId) {
            return null;
        }

        return Provider::query()->whereKey($providerId)->where('active', true)->first();
    }

    private function resolveProviderFromPin(string $pin): ?Provider
    {
        return Provider::query()
            ->where('active', true)
            ->whereIn('type', ['chapa', 'dab', 'limpieza', 'recepcion'])
            ->whereNotNull('pin')
            ->get()
            ->first(fn (Provider $provider) => Hash::check($pin, $provider->pin));
    }

    private function routeNameForProvider(Provider $provider): string
    {
        return match ($provider->type) {
            'chapa'     => 'ops.chapa',
            'dab'       => 'ops.dab',
            'limpieza'  => 'ops.limpieza',
            'recepcion' => 'ops.recepcion',
            default     => 'ops.index',
        };
    }

    private function ensureProviderType(?Provider $provider, string $expectedType): void
    {
        if (! $provider) {
            throw ValidationException::withMessages([
                'pin' => 'La sesion del operario ha expirado. Vuelve a introducir el PIN.',
            ]);
        }

        if ($provider->type !== $expectedType) {
            throw ValidationException::withMessages([
                'pin' => 'El PIN actual no corresponde a esta etapa operativa.',
            ]);
        }
    }

    private function normalizePlate(string $plate): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $plate));
    }

    private function nullableTrimmed(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function serializeChecklistItems(WorkshopOrder $order): array
    {
        return $order->checklistItems()
            ->get()
            ->map(fn (WorkshopChecklistItem $item) => [
                'id'           => $item->id,
                'label'        => $item->label,
                'sort_order'   => $item->sort_order,
                'passed'       => $item->passed,
                'has_incident' => $item->has_incident,
                'notes'        => $item->notes,
            ])
            ->all();
    }

    private function validateDamagePayload(Request $request): array
    {
        return $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'body_part' => ['required', 'string', 'max:80'],
            'x_coord' => ['required', 'numeric', 'between:0,1'],
            'y_coord' => ['required', 'numeric', 'between:0,1'],
            'severity' => ['required', 'in:leve,moderado,grave'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:10240'],
        ]);
    }
}
