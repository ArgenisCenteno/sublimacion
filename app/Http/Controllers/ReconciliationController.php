<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReconciliationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:bank_deposits.view');
    }

    public function index(): Response
    {
        $unmatchedDeposits = BankDeposit::doesntHave('payments')
            ->orderBy('deposited_at', 'desc')
            ->get();

        $unmatchedPayments = Payment::with('order')
            ->whereIn('method', ['pago_movil', 'transferencia'])
            ->whereNull('bank_deposit_id')
            ->where('status', '!=', 'rechazado')
            ->orderBy('paid_at', 'desc')
            ->get();

        return Inertia::render('Reconciliation/Index', [
            'unmatchedDeposits' => $unmatchedDeposits,
            'unmatchedPayments' => $unmatchedPayments,
        ]);
    }

    public function link(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'bank_deposit_id' => 'required|exists:bank_deposits,id',
        ]);

        $payment->update(['bank_deposit_id' => $validated['bank_deposit_id']]);

        return back()->with('success', 'Pago vinculado al depósito.');
    }
}
