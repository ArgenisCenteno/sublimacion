<?php

namespace App\Http\Controllers;

use App\Exports\GenericTableExport;
use App\Models\BankDeposit;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private const GENERIC_CUSTOMER_EMAIL = 'venta-rapida@pos.local';

    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:reportes.ver')->only(['dashboard', 'sales', 'customers', 'inventory', 'shipping']);
        $this->middleware('can:reportes.financieros.ver')->only(['financial']);
    }

    public function dashboard(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();

        $ordersQuery = fn () => $this->baseOrders($from, $to, $canal);

        $totalVendido = (float) (clone $ordersQuery())->sum('total_usd');
        $numPedidos = (clone $ordersQuery())->count();
        $pendientesPago = (clone $ordersQuery())->whereIn('status', ['pendiente_pago', 'pago_parcial'])->count();
        $enProceso = (clone $ordersQuery())->whereIn('status', ['en_preparacion', 'enviado'])->count();

        $days = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return Inertia::render('Reports/Dashboard', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'kpis' => [
                'total_vendido' => $totalVendido,
                'num_pedidos' => $numPedidos,
                'ticket_promedio' => $numPedidos > 0 ? $totalVendido / $numPedidos : 0,
                'pendientes_pago' => $pendientesPago,
                'en_proceso' => $enProceso,
            ],
            'currentSeries' => $this->dailySales($from, $to, $canal),
            'previousSeries' => $this->dailySales($prevFrom, $prevTo, $canal),
        ]);
    }

    public function sales(Request $request): Response|HttpResponse|BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();

        $orderFilter = function ($q) use ($from, $to, $canal) {
            $q->whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado');
            if ($canal) {
                $q->where('canal', $canal);
            }
        };

        $byProductRows = OrderItem::whereHas('order', $orderFilter)
            ->selectRaw('product_name, sum(quantity) as units, sum(subtotal_usd) as total')
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [$r->product_name, (int) $r->units, $this->money($r->total)])
            ->all();

        $byCategoryRows = OrderItem::whereHas('order', $orderFilter)
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("coalesce(categories.name, 'Sin categoría') as category_name, sum(order_items.quantity) as units, sum(order_items.subtotal_usd) as total")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [$r->category_name, (int) $r->units, $this->money($r->total)])
            ->all();

        $byCanalRows = Order::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelado')
            ->selectRaw('canal, count(*) as orders_count, sum(total_usd) as total')
            ->groupBy('canal')
            ->get()
            ->map(fn ($r) => [$r->canal === 'online' ? 'Online' : 'Tienda física', (int) $r->orders_count, $this->money($r->total)])
            ->all();

        $posOrders = Order::whereBetween('created_at', [$from, $to])
            ->where('canal', 'tienda_fisica')
            ->where('status', '!=', 'cancelado')
            ->with(['payments' => fn ($q) => $q->limit(1)])
            ->get();

        $byEmployeeRows = $posOrders->groupBy(fn ($o) => $o->payments->first()?->submitted_by)
            ->map(function ($orders, $userId) {
                $user = $userId ? User::find($userId) : null;

                return [$user?->name ?? 'Desconocido', $orders->count(), $this->money($orders->sum('total_usd'))];
            })
            ->sortByDesc(fn ($row) => $row[1])
            ->values()
            ->all();

        $byMethodRows = Payment::whereHas('order', $orderFilter)
            ->where('status', 'verificado')
            ->selectRaw('method, count(*) as count, sum(amount_usd) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [$this->methodLabel($r->method), (int) $r->count, $this->money($r->total)])
            ->all();

        $tables = [
            'por_producto' => ['title' => 'Ventas por producto/servicio', 'headings' => ['Producto', 'Unidades', 'Total USD'], 'rows' => $byProductRows],
            'por_categoria' => ['title' => 'Ventas por categoría', 'headings' => ['Categoría', 'Unidades', 'Total USD'], 'rows' => $byCategoryRows],
            'por_canal' => ['title' => 'Ventas por canal', 'headings' => ['Canal', 'Pedidos', 'Total USD'], 'rows' => $byCanalRows],
            'por_empleado' => ['title' => 'Ventas por empleado (tienda física)', 'headings' => ['Empleado', 'Ventas', 'Total USD'], 'rows' => $byEmployeeRows],
            'por_metodo' => ['title' => 'Ventas por método de pago', 'headings' => ['Método', 'Pagos', 'Total USD'], 'rows' => $byMethodRows],
        ];

        if ($export = $this->exportIfRequested($request, $tables)) {
            return $export;
        }

        return Inertia::render('Reports/Sales', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'tables' => $tables,
        ]);
    }

    public function financial(Request $request): Response|HttpResponse|BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();

        $orderFilter = function ($q) use ($from, $to, $canal) {
            $q->whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado');
            if ($canal) {
                $q->where('canal', $canal);
            }
        };

        $confirmados = (float) Payment::whereHas('order', $orderFilter)->where('status', 'verificado')->sum('amount_usd');
        $ordersInRange = Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado')->get();
        $pendientes = (float) $ordersInRange->sum(fn ($o) => $o->balanceDue());

        $ingresosRows = [
            ['Confirmados', $this->money($confirmados)],
            ['Pendientes', $this->money($pendientes)],
        ];

        $depositosSinPago = BankDeposit::whereBetween('deposited_at', [$from, $to])->doesntHave('payments');
        $pagosSinDeposito = Payment::whereIn('method', ['pago_movil', 'transferencia'])
            ->whereNull('bank_deposit_id')
            ->where('status', '!=', 'rechazado')
            ->whereBetween('paid_at', [$from, $to]);

        $conciliacionRows = [
            ['Depósitos sin pago vinculado', $depositosSinPago->count(), $this->money((clone $depositosSinPago)->sum('amount_usd'))],
            ['Pagos sin depósito vinculado', $pagosSinDeposito->count(), $this->money((clone $pagosSinDeposito)->sum('amount_usd'))],
        ];

        $byMethodRows = Payment::whereBetween('paid_at', [$from, $to])
            ->selectRaw("method, sum(case when status = 'pendiente' then 1 else 0 end) as pendientes, sum(case when status = 'verificado' then 1 else 0 end) as verificados, sum(case when status = 'rechazado' then 1 else 0 end) as rechazados, sum(case when status = 'verificado' then amount_usd else 0 end) as total_verificado")
            ->groupBy('method')
            ->get()
            ->map(fn ($r) => [$this->methodLabel($r->method), (int) $r->pendientes, (int) $r->verificados, (int) $r->rechazados, $this->money($r->total_verificado)])
            ->all();

        $recibosEmitidos = Receipt::whereBetween('issued_at', [$from, $to]);
        $recibosAnulados = Receipt::whereBetween('issued_at', [$from, $to])->whereNotNull('voided_at');

        $recibosResumenRows = [
            ['Emitidos', (clone $recibosEmitidos)->count(), $this->money((clone $recibosEmitidos)->sum('amount_usd'))],
            ['Anulados', (clone $recibosAnulados)->count(), $this->money((clone $recibosAnulados)->sum('amount_usd'))],
        ];

        $recibosAnuladosDetalleRows = (clone $recibosAnulados)->with('voidedBy')->get()
            ->map(fn (Receipt $r) => [$r->formatted_number, "#{$r->order_id}", $r->void_reason, $r->voidedBy?->name ?? '—', $r->voided_at->format('d/m/Y')])
            ->all();

        $cashShiftsQuery = CashShift::whereBetween('opened_at', [$from, $to])->with('user');
        if ($request->filled('employee_id')) {
            $cashShiftsQuery->where('user_id', $request->integer('employee_id'));
        }
        $cierresRows = $cashShiftsQuery->get()
            ->map(fn (CashShift $s) => [
                $s->user->name,
                $s->opened_at->format('d/m/Y H:i'),
                $s->closed_at?->format('d/m/Y H:i') ?? '—',
                $this->money($s->opening_cash_usd),
                $s->expected_cash_usd !== null ? $this->money($s->expected_cash_usd) : '—',
                $s->counted_cash_usd !== null ? $this->money($s->counted_cash_usd) : '—',
                $s->difference_usd !== null ? $this->money($s->difference_usd) : '—',
            ])
            ->all();

        $tables = [
            'ingresos' => ['title' => 'Ingresos confirmados vs pendientes', 'headings' => ['Concepto', 'Monto USD'], 'rows' => $ingresosRows],
            'conciliacion' => ['title' => 'Resumen de conciliación bancaria', 'headings' => ['Concepto', 'Cantidad', 'Monto USD'], 'rows' => $conciliacionRows],
            'pagos_por_metodo' => ['title' => 'Pagos por método', 'headings' => ['Método', 'Pendientes', 'Verificados', 'Rechazados', 'Total verificado USD'], 'rows' => $byMethodRows],
            'recibos' => ['title' => 'Recibos emitidos vs anulados', 'headings' => ['Estado', 'Cantidad', 'Monto USD'], 'rows' => $recibosResumenRows],
            'recibos_anulados' => ['title' => 'Detalle de recibos anulados', 'headings' => ['Recibo', 'Pedido', 'Motivo', 'Anulado por', 'Fecha'], 'rows' => $recibosAnuladosDetalleRows],
            'cierres_caja' => ['title' => 'Cierres de caja', 'headings' => ['Empleado', 'Apertura', 'Cierre', 'Fondo USD', 'Esperado USD', 'Contado USD', 'Diferencia USD'], 'rows' => $cierresRows],
        ];

        if ($export = $this->exportIfRequested($request, $tables)) {
            return $export;
        }

        return Inertia::render('Reports/Financial', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'tables' => $tables,
            'employees' => User::role('Empleado')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function customers(Request $request): Response|HttpResponse|BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();

        $topRows = Order::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelado')
            ->whereHas('user', fn ($q) => $q->where('email', '!=', self::GENERIC_CUSTOMER_EMAIL))
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($orders) => [
                $orders->first()->user->name,
                $orders->first()->user->email,
                $orders->count(),
                $this->money($orders->sum('total_usd')),
            ])
            ->sortByDesc(fn ($row) => (float) str_replace(['$', ','], '', $row[3]))
            ->values()
            ->take(20)
            ->all();

        $customerIds = Order::whereBetween('created_at', [$from, $to])
            ->whereHas('user', fn ($q) => $q->where('email', '!=', self::GENERIC_CUSTOMER_EMAIL))
            ->pluck('user_id')
            ->unique();

        $nuevos = 0;
        $recurrentes = 0;
        foreach ($customerIds as $userId) {
            $firstOrderDate = Order::where('user_id', $userId)->min('created_at');
            if ($firstOrderDate && CarbonImmutable::parse($firstOrderDate)->between($from, $to)) {
                $nuevos++;
            } else {
                $recurrentes++;
            }
        }

        $tables = [
            'top_clientes' => ['title' => 'Clientes más frecuentes', 'headings' => ['Cliente', 'Email', 'Pedidos', 'Total USD'], 'rows' => $topRows],
            'nuevos_recurrentes' => ['title' => 'Clientes nuevos vs recurrentes', 'headings' => ['Tipo', 'Cantidad'], 'rows' => [['Nuevos', $nuevos], ['Recurrentes', $recurrentes]]],
        ];

        if ($export = $this->exportIfRequested($request, $tables)) {
            return $export;
        }

        return Inertia::render('Reports/Customers', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'tables' => $tables,
        ]);
    }

    public function inventory(Request $request): Response|HttpResponse|BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();
        $threshold = $request->integer('threshold', 5);

        $lowStockRows = [];
        Product::where('type', 'producto')->where('active', true)->with('variants')->get()->each(function (Product $product) use (&$lowStockRows, $threshold) {
            if ($product->variants->isEmpty()) {
                if ($product->stock !== null && $product->stock <= $threshold) {
                    $lowStockRows[] = [$product->name, '—', $product->stock, $threshold];
                }

                return;
            }

            foreach ($product->variants as $variant) {
                if ($variant->stock !== null && $variant->stock <= $threshold) {
                    $lowStockRows[] = [$product->name, $variant->name, $variant->stock, $threshold];
                }
            }
        });

        $movedProductIds = OrderItem::whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado'))
            ->pluck('product_id')
            ->unique();

        $noMovementRows = Product::where('active', true)
            ->whereNotIn('id', $movedProductIds)
            ->with('category')
            ->get()
            ->map(fn (Product $p) => [$p->name, $p->category?->name ?? 'Sin categoría', $p->stock ?? '—'])
            ->all();

        $tables = [
            'stock_bajo' => ['title' => "Stock bajo (umbral: {$threshold})", 'headings' => ['Producto', 'Variante', 'Stock', 'Umbral'], 'rows' => $lowStockRows],
            'sin_movimiento' => ['title' => 'Productos sin movimiento en el período', 'headings' => ['Producto', 'Categoría', 'Stock'], 'rows' => $noMovementRows],
        ];

        if ($export = $this->exportIfRequested($request, $tables)) {
            return $export;
        }

        return Inertia::render('Reports/Inventory', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'threshold' => $threshold,
            'tables' => $tables,
        ]);
    }

    public function shipping(Request $request): Response|HttpResponse|BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $canal = $request->string('canal')->toString();

        $byStatusRows = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['en_preparacion', 'enviado', 'entregado', 'cancelado'])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->map(fn ($r) => [$this->statusLabel($r->status), (int) $r->count])
            ->all();

        $byCityRows = Order::whereBetween('created_at', [$from, $to])
            ->where('delivery_type', 'envio_nacional')
            ->selectRaw('shipping_city, shipping_carrier, count(*) as count')
            ->groupBy('shipping_city', 'shipping_carrier')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [$r->shipping_city ?? '—', $r->shipping_carrier ?? '—', (int) $r->count])
            ->all();

        $tables = [
            'por_estado' => ['title' => 'Pedidos por estado de entrega', 'headings' => ['Estado', 'Cantidad'], 'rows' => $byStatusRows],
            'por_ciudad' => ['title' => 'Envíos nacionales por ciudad/empresa', 'headings' => ['Ciudad', 'Empresa', 'Cantidad'], 'rows' => $byCityRows],
        ];

        if ($export = $this->exportIfRequested($request, $tables)) {
            return $export;
        }

        return Inertia::render('Reports/Shipping', [
            'filters' => $this->filtersPayload($from, $to, $canal),
            'tables' => $tables,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveDateRange(Request $request): array
    {
        $to = $request->filled('to') ? CarbonImmutable::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? CarbonImmutable::parse($request->string('from')->toString())->startOfDay() : now()->startOfMonth();

        return [$from, $to];
    }

    private function filtersPayload(CarbonImmutable $from, CarbonImmutable $to, string $canal): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'canal' => $canal,
        ];
    }

    private function baseOrders(CarbonImmutable $from, CarbonImmutable $to, string $canal)
    {
        $query = Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado');
        if ($canal) {
            $query->where('canal', $canal);
        }

        return $query;
    }

    private function dailySales(CarbonImmutable $from, CarbonImmutable $to, string $canal): array
    {
        $query = Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelado');
        if ($canal) {
            $query->where('canal', $canal);
        }

        $rows = $query->selectRaw('DATE(created_at) as day, SUM(total_usd) as total')
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($r) => CarbonImmutable::parse($r->day)->toDateString());

        $series = [];
        $cursor = $from;
        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $series[] = ['date' => $key, 'total' => (float) ($rows[$key]->total ?? 0)];
            $cursor = $cursor->addDay();
        }

        return $series;
    }

    private function exportIfRequested(Request $request, array $tables): HttpResponse|BinaryFileResponse|null
    {
        if (! $request->filled('export')) {
            return null;
        }

        $key = $request->string('report')->toString();
        abort_unless(isset($tables[$key]), 404, 'Reporte no encontrado.');

        $table = $tables[$key];
        $format = $request->string('export')->toString();
        $filename = str_replace('_', '-', $key);

        if ($format === 'excel') {
            return Excel::download(new GenericTableExport($table['headings'], $table['rows']), "{$filename}.xlsx");
        }

        return Pdf::loadView('reports.generic-table', [
            'title' => $table['title'],
            'subtitle' => null,
            'headings' => $table['headings'],
            'rows' => $table['rows'],
        ])->download("{$filename}.pdf");
    }

    private function money(float|string|null $value): string
    {
        return '$'.number_format((float) $value, 2);
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'pago_movil' => 'Pago móvil',
            'transferencia' => 'Transferencia',
            'efectivo' => 'Efectivo',
            'paypal' => 'PayPal',
            default => $method,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pendiente_pago' => 'Pendiente de pago',
            'pago_parcial' => 'Pago parcial',
            'pago_confirmado' => 'Pago confirmado',
            'en_preparacion' => 'En preparación',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            default => $status,
        };
    }
}
