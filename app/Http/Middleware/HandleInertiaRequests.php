<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;
use Lab404\Impersonate\Services\ImpersonateManager;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()
                    ? $request->user()->getRoleNames()
                    : [],
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')
                    : [],
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'warning' => fn() => $request->session()->get('warning'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'notifications' => fn () => $request->user() ? [
                'count' => $request->user()->unreadNotifications()->count(),
                'items' => $request->user()->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'kind' => $n->data['kind'] ?? 'order',
                    'tone' => $n->data['tone'] ?? 'sky',
                    'label' => $n->data['label'] ?? '',
                    'title' => $n->data['title'] ?? '',
                    'message' => $n->data['message'] ?? '',
                    'href' => $n->data['href'] ?? null,
                    'time' => $n->created_at->diffForHumans(),
                    'read' => $n->read_at !== null,
                ])->values(),
            ] : ['count' => 0, 'items' => []],
            'impersonating' => fn () => $request->user() && $request->user()->isImpersonated()
                ? ['name' => app(ImpersonateManager::class)->getImpersonator()?->name]
                : null,
            'cartCount' => fn () => $request->user()
                ? (int) CartItem::whereHas('cart', fn ($q) => $q->where('user_id', $request->user()->id))->sum('quantity')
                : 0,
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'store' => fn () => $this->storeSettings(),
        ];
    }

    private function storeSettings(): array
    {
        $s = StoreSetting::current();

        return [
            'name' => $s->store_name ?: config('app.name'),
            'slogan' => $s->store_slogan,
            'logoUrl' => $s->logo_path ? Storage::url($s->logo_path) : null,
            'colorPrimary' => $s->color_primary,
            'colorSecondary' => $s->color_secondary,
            'social' => [
                'instagram' => $s->instagram_url,
                'facebook' => $s->facebook_url,
                'tiktok' => $s->tiktok_url,
                'whatsapp' => $s->whatsapp_number,
            ],
            'contact' => [
                'email' => $s->contact_email,
                'phone' => $s->contact_phone,
                'address' => $s->address,
                'hours' => $s->business_hours,
            ],
            'paymentAccounts' => [
                'pagoMovil' => [
                    'bank' => $s->pago_movil_bank,
                    'phone' => $s->pago_movil_phone,
                    'cedulaRif' => $s->pago_movil_cedula_rif,
                    'holder' => $s->pago_movil_holder_name,
                ],
                'transferencia' => [
                    'bank' => $s->transferencia_bank,
                    'accountNumber' => $s->transferencia_account_number,
                    'cedulaRif' => $s->transferencia_cedula_rif,
                    'holder' => $s->transferencia_holder_name,
                ],
            ],
        ];
    }
}
