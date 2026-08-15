<?php

namespace App\Http\Controllers;

use App\Models\TasaCambio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TasaCambioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // añade middleware de rol si tienes: ->middleware('role:admin')
    }

    public function update(Request $request, string $divisa): RedirectResponse
    {
        abort_unless(in_array($divisa, ['EUR', 'BS']), 404);

        $request->validate(['tasa' => ['required', 'numeric', 'min:0.0001']]);

        TasaCambio::updateOrCreate(
            ['divisa'     => $divisa],
            ['tasa'       => $request->tasa,
             'updated_by' => auth()->id()]
        );

        return back()->with('success', "Tasa de {$divisa} actualizada.");
    }
}