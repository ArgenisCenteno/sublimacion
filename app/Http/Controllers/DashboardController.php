<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return $user->isCliente()
            ? $this->clienteDashboard($user)
            : $this->staffDashboard();
    }

    private function clienteDashboard(User $user): Response
    {
        $orders = Order::where('user_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'orders_page');

        $balanceDue = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelado')
            ->get()
            ->sum(fn (Order $order) => $order->balanceDue());

        $receipts = Receipt::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('issued_at', 'desc')
            ->paginate(10, ['*'], 'receipts_page');

        return Inertia::render('Dashboard/Cliente', [
            'orders' => $orders,
            'balanceDue' => $balanceDue,
            'receipts' => $receipts,
        ]);
    }

    private function staffDashboard(): Response
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $monthlyOrders = Order::where('created_at', '>=', $monthStart)->where('status', '!=', 'cancelado');

        $kpis = [
            'total_vendido_mes' => (float) (clone $monthlyOrders)->sum('total_usd'),
            'pedidos_mes' => (clone $monthlyOrders)->count(),
            'pedidos_hoy' => Order::where('created_at', '>=', $today)->where('status', '!=', 'cancelado')->count(),
            'pendientes_pago' => Order::whereIn('status', ['pendiente_pago', 'pago_parcial'])->count(),
        ];

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $pendingPayments = Payment::with('order')
            ->where('status', 'pendiente')
            ->orderBy('created_at')
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'kpis' => $kpis,
            'recentOrders' => $recentOrders,
            'pendingPayments' => $pendingPayments,
        ]);
    }
}
