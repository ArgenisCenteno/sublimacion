<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BankDepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:bank_deposits.view')->only(['index', 'show']);
        $this->middleware('can:bank_deposits.create')->only(['create', 'store']);
        $this->middleware('can:bank_deposits.edit')->only(['edit', 'update']);
        $this->middleware('can:bank_deposits.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $query = BankDeposit::with('registeredBy');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('bank_name', 'like', "%{$request->search}%")
                    ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        $deposits = $query->orderBy('deposited_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('BankDeposits/Index', [
            'deposits' => $deposits,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BankDeposits/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDeposit($request);
        $validated['registered_by'] = $request->user()->id;

        BankDeposit::create($validated);

        return redirect()->route('bank-deposits.index')->with('success', 'Depósito registrado correctamente.');
    }

    public function edit(BankDeposit $bankDeposit): Response
    {
        return Inertia::render('BankDeposits/Edit', [
            'deposit' => $bankDeposit,
        ]);
    }

    public function update(Request $request, BankDeposit $bankDeposit): RedirectResponse
    {
        $validated = $this->validateDeposit($request);

        $bankDeposit->update($validated);

        return redirect()->route('bank-deposits.index')->with('success', 'Depósito actualizado correctamente.');
    }

    public function destroy(BankDeposit $bankDeposit): RedirectResponse
    {
        $bankDeposit->delete();

        return redirect()->route('bank-deposits.index')->with('success', 'Depósito eliminado correctamente.');
    }

    private function validateDeposit(Request $request): array
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'amount_bs' => 'nullable|numeric|min:0',
            'amount_usd' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'deposited_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['amount_bs']) && empty($validated['amount_usd'])) {
            abort(422, 'Debes indicar al menos un monto (Bs o USD).');
        }

        return $validated;
    }
}
