@php
    $money = fn ($n) => number_format((float) $n, 2, ',', '.');
    $brand = $store->color_primary ?: '#42AAAB';
    $storeName = $store->store_name ?: config('app.name');
    $valid = $receipt->voided_at === null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verificación {{ $receipt->formatted_number }} · {{ $storeName }}</title>
    <style>
        :root { --brand: {{ $brand }}; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; color: #1f2937; }
        .card { width: 100%; max-width: 420px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.08); overflow: hidden; }
        .top { padding: 28px 24px 22px; text-align: center; color: #fff; background: {{ $valid ? 'var(--brand)' : '#dc2626' }}; }
        .icon { width: 56px; height: 56px; margin: 0 auto 10px; border-radius: 50%; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; }
        .top h1 { margin: 0; font-size: 20px; }
        .top p { margin: 4px 0 0; opacity: .9; font-size: 14px; }
        dl { margin: 0; padding: 8px 24px 4px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f1f3; font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        dt { color: #6b7280; }
        dd { margin: 0; font-weight: 600; text-align: right; }
        .note { margin: 8px 24px 24px; padding: 12px 14px; border-radius: 12px; font-size: 13px; background: #fef2f2; color: #991b1b; }
        .foot { padding: 14px 24px 22px; text-align: center; font-size: 12px; color: #9ca3af; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b0f17; color: #e5e7eb; }
            .card { background: #111827; box-shadow: none; }
            .row { border-color: #1f2937; }
            dt { color: #9ca3af; }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="top">
            <div class="icon">{{ $valid ? '✓' : '✕' }}</div>
            <h1>{{ $valid ? 'Recibo válido' : 'Recibo anulado' }}</h1>
            <p>Emitido por {{ $storeName }}</p>
        </div>

        <dl>
            <div class="row"><dt>Recibo</dt><dd>{{ $receipt->formatted_number }}</dd></div>
            <div class="row"><dt>Fecha</dt><dd>{{ $receipt->issued_at->format('d/m/Y h:i A') }}</dd></div>
            <div class="row"><dt>Pedido</dt><dd>#{{ $receipt->order_id }}</dd></div>
            <div class="row"><dt>Cliente</dt><dd>{{ $receipt->customer_name }}</dd></div>
            <div class="row"><dt>Método</dt><dd>{{ $paymentLabels[$receipt->payment_method] ?? $receipt->payment_method }}</dd></div>
            <div class="row">
                <dt>Monto</dt>
                <dd>
                    ${{ $money($receipt->amount_usd) }}
                    @if($receipt->amount_bs)<br><span style="font-weight: 400; color: #6b7280;">Bs. {{ $money($receipt->amount_bs) }}</span>@endif
                </dd>
            </div>
        </dl>

        @unless($valid)
            <div class="note">Anulado el {{ $receipt->voided_at->format('d/m/Y') }}. Motivo: {{ $receipt->void_reason }}</div>
        @endunless

        <div class="foot">Verificación generada desde el código QR del recibo.</div>
    </main>
</body>
</html>
