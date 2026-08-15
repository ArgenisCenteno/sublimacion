<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\CostEntry;
use App\Models\Invoice;
use App\Models\Muestra;
use App\Models\OrdenExamen;
use App\Models\OrdenTrabajo;
use App\Models\RentingContract;
use App\Models\SaleContract;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleWorkflowPlan;
use App\Models\WorkshopOrder;
use App\Services\Alerts\AlertCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // ── Hero cards ────────────────────────────────────────────────────
        $ordenes_hoy = OrdenTrabajo::whereDate('created_at', today())->count();

        $muestras_pendientes = Muestra::where('estado', 'pendiente')->count();

        $resultados_pendientes = OrdenExamen::whereIn('estado', ['muestra_tomada', 'en_proceso'])->count();

        $ordenes_listas = OrdenTrabajo::where('estado', 'listo')->count();

        // ── Órdenes recientes ─────────────────────────────────────────────
        $ordenes_recientes = OrdenTrabajo::with(['paciente', 'medico'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($o) => [
                'id'              => $o->id,
                'codigo'          => $o->codigo,
                'estado'          => $o->estado,
                'estado_label'    => $o->estado_label,
                'estado_pago'     => $o->estado_pago,
                'total'           => $o->total,
                'created_at'      => $o->created_at->format('d/m H:i'),
                'paciente'        => $o->paciente ? [
                    'nombre' => "{$o->paciente->apellidos}, {$o->paciente->nombres}",
                    'cedula' => "{$o->paciente->tipo_documento}-{$o->paciente->cedula}",
                ] : null,
                'medico' => $o->medico?->nombre_completo,
            ]);

        // ── Cola de muestras ──────────────────────────────────────────────
        $cola_muestras = OrdenTrabajo::with('paciente')
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereHas('muestras', fn ($q) => $q->where('estado', 'pendiente'))
            ->withCount(['muestras as muestras_pendientes' => fn ($q) => $q->where('estado', 'pendiente')])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'id'                 => $o->id,
                'codigo'             => $o->codigo,
                'muestras_pendientes'=> $o->muestras_pendientes,
                'paciente'           => $o->paciente?->apellidos . ', ' . $o->paciente?->nombres,
            ]);

        // ── Estadísticas por estado ───────────────────────────────────────
        $por_estado = OrdenTrabajo::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // ── Estadísticas de pago ──────────────────────────────────────────
        $por_pago = OrdenTrabajo::select('estado_pago', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', today())
            ->groupBy('estado_pago')
            ->pluck('total', 'estado_pago');

        return Inertia::render('Dashboard/Index', [
            'hero' => [
                'ordenes_hoy'          => $ordenes_hoy,
                'muestras_pendientes'  => $muestras_pendientes,
                'resultados_pendientes'=> $resultados_pendientes,
                'ordenes_listas'       => $ordenes_listas,
            ],
            'ordenes' => [
                'recientes'    => $ordenes_recientes,
                'por_estado'   => [
                    'pendiente'  => (int) ($por_estado['pendiente']  ?? 0),
                    'en_proceso' => (int) ($por_estado['en_proceso'] ?? 0),
                    'listo'      => (int) ($por_estado['listo']      ?? 0),
                    'entregado'  => (int) ($por_estado['entregado']  ?? 0),
                    'anulado'    => (int) ($por_estado['anulado']    ?? 0),
                ],
                'pagos_hoy' => [
                    'pendiente'  => (int) ($por_pago['pendiente']  ?? 0),
                    'pagado'     => (int) ($por_pago['pagado']     ?? 0),
                    'exonerado'  => (int) ($por_pago['exonerado']  ?? 0),
                ],
            ],
            'laboratorio' => [
                'cola_muestras'        => $cola_muestras,
                'muestras_pendientes'  => $muestras_pendientes,
                'resultados_pendientes'=> $resultados_pendientes,
            ],
        ]);
    }

    public function drilldown(Request $request, AlertCenterService $alertCenterService): JsonResponse
    {
        $alertCenterService->sync();

        $key = (string) $request->query('key', '');

        $payload = match ($key) {
            'hero_fleet_total' => $this->vehicleDrilldown(
                'Flota total',
                'Todos los vehiculos registrados en el sistema.',
                Vehicle::query()->orderByDesc('updated_at')
            ),
            'hero_available_now', 'fleet_physical_disponible' => $this->vehicleDrilldown(
                'Vehiculos disponibles',
                'Vehiculos listos para moverse o gestionarse.',
                Vehicle::query()->where('physical_status', 'disponible')->orderBy('plate')
            ),
            'hero_in_workshop_now' => $this->vehicleDrilldown(
                'Vehiculos en taller',
                'Vehiculos que ahora mismo estan en chapa, DAB o limpieza.',
                Vehicle::query()->whereIn('physical_status', ['taller_chapa', 'taller_dab', 'taller_limpieza'])->orderBy('plate')
            ),
            'hero_alerts_pending', 'alerts_pending' => $this->alertDrilldown(
                'Alertas pendientes',
                'Alertas activas que todavia necesitan revision.',
                Alert::query()->pending()->with('vehicle:id,plate,brand,model')->latest('created_at')
            ),
            'alerts_high' => $this->alertDrilldown(
                'Alertas de prioridad alta',
                'Incidencias criticas que conviene revisar cuanto antes.',
                Alert::query()->where('status', 'pendiente')->where('priority', 'alta')->with('vehicle:id,plate,brand,model')->latest('created_at')
            ),
            'alerts_medium' => $this->alertDrilldown(
                'Alertas de prioridad media',
                'Tareas de seguimiento que siguen abiertas.',
                Alert::query()->where('status', 'pendiente')->where('priority', 'media')->with('vehicle:id,plate,brand,model')->latest('created_at')
            ),
            'alerts_resolved_today' => $this->alertDrilldown(
                'Alertas resueltas hoy',
                'Alertas cerradas o descartadas durante el dia de hoy.',
                Alert::query()->whereIn('status', ['resuelta', 'descartada'])->whereDate('resolved_at', today())->with('vehicle:id,plate,brand,model')->latest('resolved_at')
            ),
            'fleet_physical_en_transito' => $this->vehicleDrilldown('Vehiculos en transito', 'Vehiculos que aun no han llegado a campa.', Vehicle::query()->where('physical_status', 'en_transito')->orderBy('plate')),
            'fleet_physical_en_campa' => $this->vehicleDrilldown('Vehiculos en campa', 'Vehiculos ubicados actualmente en campa.', Vehicle::query()->where('physical_status', 'en_campa')->orderBy('plate')),
            'fleet_physical_taller_chapa' => $this->vehicleDrilldown('Vehiculos en chapa', 'Vehiculos que estan pasando por chapa.', Vehicle::query()->where('physical_status', 'taller_chapa')->orderBy('plate')),
            'fleet_physical_taller_dab' => $this->vehicleDrilldown('Vehiculos en DAB', 'Vehiculos que estan pasando por DAB.', Vehicle::query()->where('physical_status', 'taller_dab')->orderBy('plate')),
            'fleet_physical_taller_limpieza' => $this->vehicleDrilldown('Vehiculos en limpieza', 'Vehiculos que estan pasando por limpieza.', Vehicle::query()->where('physical_status', 'taller_limpieza')->orderBy('plate')),
            'fleet_physical_pendiente_recogida', 'operations_pending_pickup' => $this->vehicleDrilldown('Pendientes de recogida', 'Vehiculos listos para coordinar recogida.', Vehicle::query()->where('physical_status', 'pendiente_recogida')->orderBy('plate')),
            'fleet_physical_entregado' => $this->vehicleDrilldown('Vehiculos entregados', 'Vehiculos ya entregados al cliente.', Vehicle::query()->where('physical_status', 'entregado')->orderBy('plate')),
            'fleet_commercial_no_publicable' => $this->vehicleDrilldown('Vehiculos no publicables', 'Vehiculos que todavia no estan listos para publicarse.', Vehicle::query()->where('commercial_status', 'no_publicable')->orderBy('plate')),
            'fleet_commercial_disponible' => $this->vehicleDrilldown('Vehiculos comercialmente disponibles', 'Vehiculos disponibles para reserva o venta.', Vehicle::query()->where('commercial_status', 'disponible')->orderBy('plate')),
            'fleet_commercial_reservado' => $this->vehicleDrilldown(
                'Vehiculos reservados',
                'Vehiculos cuyo estado comercial actual es reservado.',
                Vehicle::query()->where('commercial_status', 'reservado')->orderBy('plate')
            ),
            'fleet_commercial_vendido' => $this->vehicleDrilldown(
                'Vehiculos vendidos',
                'Vehiculos cuyo estado comercial actual es vendido.',
                Vehicle::query()->where('commercial_status', 'vendido')->orderBy('plate')
            ),
            'commercial_reserved' => $this->saleDrilldown(
                'Reservas activas',
                'Contratos de reserva activos registrados en el sistema.',
                SaleContract::query()->where('status', 'reservado')->with(['vehicle:id,plate,brand,model', 'client:id,name'])->latest('updated_at')
            ),
            'commercial_sold_month' => $this->saleDrilldown(
                'Vehiculos vendidos este mes',
                'Ventas cerradas dentro del mes actual.',
                SaleContract::query()->where('status', 'vendido')->whereBetween('signed_at', [now()->startOfMonth(), now()->endOfMonth()])->with(['vehicle:id,plate,brand,model', 'client:id,name'])->latest('signed_at')
            ),
            'documents_vehicles_pending' => $this->vehicleDrilldown(
                'Vehiculos con documentacion pendiente',
                'Vehiculos en fase comercial relevante a los que les falta documentacion obligatoria.',
                Vehicle::query()
                    ->whereIn('commercial_status', ['disponible', 'reservado', 'vendido'])
                    ->where(function ($query) {
                        $query->where('has_ficha_tecnica', false)->orWhere('has_permiso_circulacion', false);
                    })
                    ->orderBy('plate')
            ),
            'documents_missing_ficha_tecnica' => $this->vehicleDrilldown(
                'Falta ficha tecnica',
                'Vehiculos que aun no tienen la ficha tecnica cargada.',
                Vehicle::query()->whereIn('commercial_status', ['disponible', 'reservado', 'vendido'])->where('has_ficha_tecnica', false)->orderBy('plate')
            ),
            'documents_missing_permiso_circulacion' => $this->vehicleDrilldown(
                'Falta permiso de circulacion',
                'Vehiculos que aun no tienen el permiso de circulacion cargado.',
                Vehicle::query()->whereIn('commercial_status', ['disponible', 'reservado', 'vendido'])->where('has_permiso_circulacion', false)->orderBy('plate')
            ),
            'documents_vault_pending' => $this->documentDrilldown('Documentos pendientes', 'Documentos del expediente pendientes de revisar o procesar.', VehicleDocument::query()->where('status', 'pendiente')->with('vehicle:id,plate,brand,model')->latest('created_at')),
            'documents_vault_downloaded' => $this->documentDrilldown('Documentos descargados', 'Documentos descargados y pendientes de la siguiente accion.', VehicleDocument::query()->where('status', 'descargado')->with('vehicle:id,plate,brand,model')->latest('updated_at')),
            'documents_vault_processed' => $this->documentDrilldown('Documentos procesados', 'Documentos ya procesados en la boveda documental.', VehicleDocument::query()->where('status', 'procesado')->with('vehicle:id,plate,brand,model')->latest('updated_at')),
            'commercial_renting_active' => $this->rentingDrilldown('Renting activos', 'Contratos de renting que estan activos en este momento.', RentingContract::query()->active()->with(['vehicle:id,plate,brand,model', 'client:id,name'])->orderBy('end_date')),
            'commercial_renting_expiring_30' => $this->rentingDrilldown('Renting por vencer', 'Contratos de renting que vencen en los proximos 30 dias.', RentingContract::query()->expiringSoon(30)->with(['vehicle:id,plate,brand,model', 'client:id,name'])->orderBy('end_date')),
            'finance_invoices_pending_assignment' => $this->invoiceDrilldown('Facturas pendientes', 'Facturas que todavia no se han imputado o terminado de gestionar.', Invoice::query()->where('status', 'pendiente')->with('provider:id,name')->latest('issued_at')),
            'finance_invoices_pending_ocr' => $this->invoiceDrilldown('OCR pendiente', 'Facturas pendientes de lectura automatica.', Invoice::query()->where('ocr_status', 'pendiente')->with('provider:id,name')->latest('issued_at')),
            'finance_invoices_ocr_error' => $this->invoiceDrilldown('OCR con error', 'Facturas cuya lectura automatica necesita revision manual.', Invoice::query()->where('ocr_status', 'error')->with('provider:id,name')->latest('issued_at')),
            'operations_order_pendiente' => $this->workshopOrderDrilldown('Ordenes pendientes', 'Ordenes de taller pendientes de iniciar.', WorkshopOrder::query()->where('status', 'pendiente')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_order_en_proceso' => $this->workshopOrderDrilldown('Ordenes en proceso', 'Ordenes de taller que estan actualmente en marcha.', WorkshopOrder::query()->where('status', 'en_proceso')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_order_incidencia' => $this->workshopOrderDrilldown('Ordenes con incidencia', 'Ordenes con alguna incidencia abierta.', WorkshopOrder::query()->where('status', 'incidencia')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_order_finalizado' => $this->workshopOrderDrilldown('Ordenes finalizadas', 'Ordenes de taller cerradas recientemente.', WorkshopOrder::query()->where('status', 'finalizado')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('exit_at')),
            'operations_by_type_chapa' => $this->workshopOrderDrilldown('Carga de chapa', 'Ordenes activas que estan pasando por chapa.', WorkshopOrder::query()->active()->where('type', 'chapa')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_by_type_dab' => $this->workshopOrderDrilldown('Carga de DAB', 'Ordenes activas que estan pasando por DAB.', WorkshopOrder::query()->active()->where('type', 'dab')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_by_type_limpieza' => $this->workshopOrderDrilldown('Carga de limpieza', 'Ordenes activas que estan pasando por limpieza.', WorkshopOrder::query()->active()->where('type', 'limpieza')->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->latest('entry_at')),
            'operations_bottlenecks' => $this->workshopOrderDrilldown('Vehiculos bloqueados', 'Ordenes en proceso que ya superaron el tiempo esperado en taller.', WorkshopOrder::query()->bottleneck()->with(['vehicle:id,plate,brand,model', 'provider:id,name'])->orderBy('entry_at')),
            default => [
                'title' => 'Sin datos',
                'subtitle' => 'No encontramos un desglose disponible para este indicador.',
                'emptyMessage' => 'Este indicador todavia no tiene una lista asociada.',
                'items' => [],
            ],
        };

        return response()->json($payload);
    }

    private function vehicleDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->with('provider:id,name')->limit(100)->get()->map(fn (Vehicle $vehicle) => [
            'id' => $vehicle->id,
            'title' => $vehicle->plate ?: 'Vehiculo sin matricula',
            'subtitle' => trim(collect([$vehicle->brand, $vehicle->model, $vehicle->version])->filter()->implode(' ')) ?: 'Sin descripcion disponible',
            'meta' => collect([
                $this->vehiclePhysicalStatusLabel($vehicle->physical_status),
                $this->vehicleCommercialStatusLabel($vehicle->commercial_status),
            ])->filter()->values()->all(),
            'badge' => $vehicle->provider?->name,
            'href' => route('vehicles.show', $vehicle),
            'hrefLabel' => 'Ver vehiculo',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay vehiculos para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function alertDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (Alert $alert) => [
            'id' => $alert->id,
            'title' => $this->alertTypeLabel($alert->type),
            'subtitle' => $alert->vehicle
                ? trim($alert->vehicle->plate . ' - ' . trim($alert->vehicle->brand . ' ' . $alert->vehicle->model), ' -')
                : 'Alerta sin vehiculo asociado',
            'meta' => collect([
                'Prioridad ' . $alert->priority,
                optional($alert->created_at)->format('d/m/Y H:i'),
            ])->filter()->values()->all(),
            'badge' => ucfirst($alert->status),
            'href' => route('alerts'),
            'hrefLabel' => 'Ir a alertas',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay alertas para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function workshopOrderDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (WorkshopOrder $order) => [
            'id' => $order->id,
            'title' => $order->vehicle?->plate ?: 'Orden sin matricula',
            'subtitle' => trim(collect([
                $this->workshopTypeLabel($order->type),
                $order->provider?->name,
            ])->filter()->implode(' - ')) ?: 'Sin detalles adicionales',
            'meta' => collect([
                'Estado ' . $this->workshopStatusLabel($order->status),
                $order->entry_at ? 'Entrada ' . $order->entry_at->format('d/m/Y') : null,
                $order->days_in_workshop ? $order->days_in_workshop . ' dias en taller' : null,
            ])->filter()->values()->all(),
            'badge' => $order->vehicle ? trim($order->vehicle->brand . ' ' . $order->vehicle->model) : null,
            'href' => route('workshop.orders.show', $order),
            'hrefLabel' => 'Ver orden',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay ordenes para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function documentDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (VehicleDocument $document) => [
            'id' => $document->id,
            'title' => $document->original_filename ?: 'Documento #' . $document->id,
            'subtitle' => $document->vehicle
                ? trim($document->vehicle->plate . ' - ' . trim($document->vehicle->brand . ' ' . $document->vehicle->model), ' -')
                : 'Documento sin vehiculo asociado',
            'meta' => collect([
                'Estado ' . $document->status,
                optional($document->updated_at)->format('d/m/Y H:i'),
            ])->filter()->values()->all(),
            'badge' => $document->type ?: null,
            'href' => $document->vehicle ? route('vehicles.show', $document->vehicle) : route('documents.index'),
            'hrefLabel' => $document->vehicle ? 'Ver vehiculo' : 'Ir a documentos',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay documentos para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function saleDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (SaleContract $sale) => [
            'id' => $sale->id,
            'title' => $sale->vehicle?->plate ?: 'Venta sin matricula',
            'subtitle' => trim(collect([
                $sale->vehicle ? trim($sale->vehicle->brand . ' ' . $sale->vehicle->model) : null,
                $sale->client?->name,
            ])->filter()->implode(' - ')) ?: 'Sin detalles adicionales',
            'meta' => collect([
                'Estado ' . $this->saleStatusLabel($sale->status),
                $sale->signed_at ? $sale->signed_at->format('d/m/Y') : null,
                'Importe ' . number_format((float) $sale->pvp, 0, ',', '.') . ' EUR',
            ])->filter()->values()->all(),
            'badge' => null,
            'href' => $sale->vehicle ? route('vehicles.show', $sale->vehicle) : route('sales.index'),
            'hrefLabel' => $sale->vehicle ? 'Ver vehiculo' : 'Ir a ventas',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay operaciones comerciales para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function rentingDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (RentingContract $contract) => [
            'id' => $contract->id,
            'title' => $contract->vehicle?->plate ?: 'Renting sin matricula',
            'subtitle' => trim(collect([
                $contract->vehicle ? trim($contract->vehicle->brand . ' ' . $contract->vehicle->model) : null,
                $contract->client?->name,
            ])->filter()->implode(' - ')) ?: 'Sin detalles adicionales',
            'meta' => collect([
                'Estado ' . $this->rentingStatusLabel($contract->status),
                $contract->end_date ? 'Vence ' . $contract->end_date->format('d/m/Y') : null,
                'Cuota ' . number_format((float) $contract->monthly_fee, 0, ',', '.') . ' EUR',
            ])->filter()->values()->all(),
            'badge' => $contract->days_until_expiry >= 0 ? $contract->days_until_expiry . ' dias' : null,
            'href' => $contract->vehicle ? route('vehicles.show', $contract->vehicle) : route('renting.index'),
            'hrefLabel' => $contract->vehicle ? 'Ver vehiculo' : 'Ir a renting',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay contratos de renting para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function invoiceDrilldown(string $title, string $subtitle, $query): array
    {
        $items = $query->limit(100)->get()->map(fn (Invoice $invoice) => [
            'id' => $invoice->id,
            'title' => $invoice->invoice_number ?: 'Factura #' . $invoice->id,
            'subtitle' => $invoice->provider?->name ?: 'Proveedor sin nombre',
            'meta' => collect([
                'Estado ' . $invoice->status,
                'OCR ' . $invoice->ocr_status,
                $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : null,
            ])->filter()->values()->all(),
            'badge' => number_format((float) $invoice->total_amount, 0, ',', '.') . ' EUR',
            'href' => route('invoices.show', $invoice),
            'hrefLabel' => 'Ver factura',
        ])->values()->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'emptyMessage' => 'No hay facturas para mostrar en esta vista.',
            'items' => $items,
        ];
    }

    private function vehiclePhysicalStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'en_transito' => 'En transito',
            'en_campa' => 'En campa',
            'taller_chapa' => 'En chapa',
            'taller_dab' => 'En DAB',
            'taller_limpieza' => 'En limpieza',
            'pendiente_recogida' => 'Pendiente de recogida',
            'disponible' => 'Disponible',
            'entregado' => 'Entregado',
            default => $status,
        };
    }

    private function vehicleCommercialStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'no_publicable' => 'No publicable',
            'disponible' => 'Disponible para venta',
            'reservado' => 'Reservado',
            'vendido' => 'Vendido',
            default => $status,
        };
    }

    private function alertTypeLabel(string $type): string
    {
        return match ($type) {
            'insurance_active' => 'Seguro activo',
            'key_missing' => 'Llave pendiente',
            'documentation_incomplete' => 'Documentacion pendiente',
            'bottleneck' => 'Cuello de botella',
            'taller_exit' => 'Salida de taller',
            default => str_replace('_', ' ', $type),
        };
    }

    private function workshopTypeLabel(?string $type): ?string
    {
        return match ($type) {
            'chapa' => 'Chapa',
            'dab' => 'DAB',
            'limpieza' => 'Limpieza',
            default => $type,
        };
    }

    private function workshopStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'incidencia' => 'Con incidencia',
            'finalizado' => 'Finalizada',
            default => $status,
        };
    }

    private function saleStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'reservado' => 'Reservado',
            'vendido' => 'Vendido',
            'reabierto' => 'Reabierto',
            'cancelado' => 'Cancelado',
            default => $status,
        };
    }

    private function rentingStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'activo' => 'Activo',
            'vencido' => 'Vencido',
            'renovado' => 'Renovado',
            default => $status,
        };
    }
}
