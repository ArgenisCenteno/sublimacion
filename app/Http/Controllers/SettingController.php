<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:settings.edit')->only(['update', 'store', 'updateStore']);
    }

    public function edit(): Response
    {
        return Inertia::render('Settings/ExchangeRate', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exchange_rate_bs' => 'required|numeric|min:0.01',
        ]);

        $validated['exchange_rate_bs'] = round((float) $validated['exchange_rate_bs'], 2);

        StoreSetting::current()->update($validated);

        return back()->with('success', 'Tasa de cambio actualizada.');
    }

    public function store(): Response
    {
        return Inertia::render('Settings/Store', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'store_slogan' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'color_primary' => 'required|string|max:20',
            'color_secondary' => 'required|string|max:20',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'business_hours' => 'nullable|string|max:255',
            'pago_movil_bank' => 'nullable|string|max:255',
            'pago_movil_phone' => 'nullable|string|max:30',
            'pago_movil_cedula_rif' => 'nullable|string|max:20',
            'pago_movil_holder_name' => 'nullable|string|max:255',
            'transferencia_bank' => 'nullable|string|max:255',
            'transferencia_account_number' => 'nullable|string|max:30',
            'transferencia_cedula_rif' => 'nullable|string|max:20',
            'transferencia_holder_name' => 'nullable|string|max:255',
        ]);

        unset($validated['logo']);

        $setting = StoreSetting::current();

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('store', 'public');
        }

        $setting->update($validated);

        return back()->with('success', 'Configuración de tienda actualizada.');
    }
}
