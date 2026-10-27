<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:cash_shifts.view')->only(['index', 'show']);
        $this->middleware('can:cash_shifts.reopen')->only(['reopen']);
    }

    public function index(Request $request): Response
    {
        $query = CashShift::with('user');

        if (! $request->user()->can('cash_shifts.view_all')) {
            $query->where('user_id', $request->user()->id);
        }

        $shifts = $query->orderBy('opened_at', 'desc')->paginate(15);

        return Inertia::render('CashShifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function show(Request $request, CashShift $shift): Response
    {
        abort_unless($request->user()->can('cash_shifts.view_all') || $shift->user_id === $request->user()->id, 403);

        $shift->load(['user', 'reopenedBy', 'payments.order']);

        return Inertia::render('CashShifts/Show', [
            'shift' => $shift,
            'totalsByMethod' => $shift->totalsByMethod(),
        ]);
    }

    public function reopen(Request $request, CashShift $shift): RedirectResponse
    {
        abort_unless($shift->status === 'cerrado', 422, 'Este turno no está cerrado.');

        $validated = $request->validate([
            'reopen_reason' => 'required|string|max:255',
        ]);

        $shift->update([
            'status' => 'abierto',
            'reopened_at' => now(),
            'reopened_by' => $request->user()->id,
            'reopen_reason' => $validated['reopen_reason'],
        ]);

        return back()->with('success', 'Turno reabierto correctamente.');
    }
}
