@php
    $money = fn ($n) => number_format((float) $n, 2, ',', '.');
    $usd = fn ($n) => '$' . $money($n);
    $bs = fn ($n) => 'Bs. ' . $money($n);
    $brand = $store->color_primary ?: '#42AAAB';
    $storeName = $store->store_name ?: config('app.name');
    $method = $paymentLabels[$receipt->payment_method] ?? $receipt->payment_method;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $receipt->formatted_number }}</title>
    <style>
        @page { margin: 36px 40px 70px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .muted { color: #6b7280; }
        .small { font-size: 9px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .brand { color: {{ $brand }}; }

        /* Encabezado */
        .header td { padding: 0; }
        .logo { width: 54px; height: 54px; border-radius: 10px; }
        .logo-fallback { width: 54px; height: 54px; border-radius: 10px; background: {{ $brand }}; }
        .logo-fallback td { height: 54px; color: #fff; font-size: 24px; font-weight: bold; text-align: center; vertical-align: middle; padding: 0; }
        .store-name { font-size: 17px; font-weight: bold; color: #111827; margin: 0; }
        .doc-box { border: 1.5px solid {{ $brand }}; border-radius: 8px; padding: 10px 14px; text-align: right; }
        .doc-title { font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; color: {{ $brand }}; font-weight: bold; }
        .doc-number { font-size: 18px; font-weight: bold; color: #111827; margin-top: 2px; }
        .bar { height: 4px; background: {{ $brand }}; border-radius: 2px; margin: 16px 0 18px; }

        /* Bloques de datos */
        .section-title { font-size: 8.5px; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7280; font-weight: bold; margin-bottom: 6px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; }
        .kv td { padding: 2px 0; }
        .kv td.k { color: #6b7280; width: 92px; }

        /* Detalle */
        .items { margin-top: 18px; }
        .items th { background: #f3f4f6; color: #374151; font-size: 8.5px; text-transform: uppercase; letter-spacing: .8px; padding: 7px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .items td { padding: 8px; border-bottom: 1px solid #f0f1f3; }
        .items tr.alt td { background: #fafafa; }
        .items .num { text-align: right; white-space: nowrap; }

        /* Totales */
        .totals td { padding: 4px 8px; }
        .totals .grand td { border-top: 1.5px solid #111827; font-size: 13px; font-weight: bold; padding-top: 7px; }
        .paid-box { background: {{ $brand }}; color: #fff; border-radius: 8px; padding: 12px 14px; }
        .paid-box .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.2px; opacity: .9; }
        .paid-box .amount { font-size: 20px; font-weight: bold; margin-top: 2px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8.5px; font-weight: bold; }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fef3c7; color: #92400e; }

        .qr { width: 96px; height: 96px; }
        .footer { position: fixed; bottom: -46px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }

        .watermark { position: fixed; top: 300px; left: 40px; font-size: 96px; font-weight: bold; color: rgba(220, 38, 38, 0.18); transform: rotate(-28deg); z-index: 10; }
        .void-note { margin-top: 16px; border: 1px solid #fca5a5; background: #fef2f2; color: #991b1b; padding: 10px 12px; border-radius: 8px; }
    </style>
</head>
<body>
    @if($receipt->voided_at)
        <div class="watermark">ANULADO</div>
    @endif

    <div class="footer">
        {{ $storeName }}
        @if($store->contact_phone) · {{ $store->contact_phone }} @endif
        @if($store->contact_email) · {{ $store->contact_email }} @endif
        <br>Este documento es un comprobante de pago y no sustituye la factura fiscal.
    </div>

    {{-- Encabezado --}}
    <table class="header">
        <tr>
            <td style="width: 64px;">
                @if($logo)
                    <img src="{{ $logo }}" class="logo" alt="">
                @else
                    <table class="logo-fallback"><tr><td>{{ mb_strtoupper(mb_substr($storeName, 0, 1)) }}</td></tr></table>
                @endif
            </td>
            <td style="padding-left: 4px;">
                <p class="store-name">{{ $storeName }}</p>
                @if($store->store_slogan)<div class="muted">{{ $store->store_slogan }}</div>@endif
                <div class="muted small" style="margin-top: 3px;">
                    @if($store->address){{ $store->address }}<br>@endif
                    @if($store->contact_phone)Tel. {{ $store->contact_phone }}@endif
                    @if($store->contact_phone && $store->contact_email) · @endif
                    @if($store->contact_email){{ $store->contact_email }}@endif
                </div>
            </td>
            <td style="width: 190px;">
                <div class="doc-box">
                    <div class="doc-title">Recibo de pago</div>
                    <div class="doc-number">{{ $receipt->formatted_number }}</div>
                    <div class="muted small">Emitido: {{ $receipt->issued_at->format('d/m/Y h:i A') }}</div>
                    <div class="muted small">Pedido #{{ $order->id }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="bar"></div>

    {{-- Cliente y pago --}}
    <table>
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div class="section-title">Cliente</div>
                <div class="card">
                    <table class="kv">
                        <tr><td class="k">Nombre</td><td class="bold">{{ $receipt->customer_name }}</td></tr>
                        @if($receipt->customer_cedula_rif)
                            <tr><td class="k">Cédula/RIF</td><td>{{ $receipt->customer_cedula_rif }}</td></tr>
                        @endif
                        @if($receipt->customer_email && $receipt->customer_email !== 'venta-rapida@pos.local')
                            <tr><td class="k">Email</td><td>{{ $receipt->customer_email }}</td></tr>
                        @endif
                        @if($order->customer_phone)
                            <tr><td class="k">Teléfono</td><td>{{ $order->customer_phone }}</td></tr>
                        @endif
                    </table>
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div class="section-title">Pago</div>
                <div class="card">
                    <table class="kv">
                        <tr><td class="k">Método</td><td class="bold">{{ $method }}</td></tr>
                        @if($receipt->reference)
                            <tr><td class="k">Referencia</td><td>{{ $receipt->reference }}</td></tr>
                        @endif
                        @if($receipt->amount_bs)
                            <tr><td class="k">Tasa</td><td>{{ $bs($receipt->exchange_rate) }} / $1</td></tr>
                        @endif
                        <tr><td class="k">Canal</td><td>{{ $order->canal === 'tienda_fisica' ? 'Tienda física' : 'Tienda en línea' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Detalle de la compra --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 44px;" class="center">Cant.</th>
                <th>Descripción</th>
                <th class="num" style="width: 90px;">Precio unit.</th>
                <th class="num" style="width: 90px;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
                <tr class="{{ $i % 2 ? 'alt' : '' }}">
                    <td class="center bold">{{ $item->quantity }}</td>
                    <td>
                        <span class="bold">{{ $item->product_name }}</span>
                        @if($item->variant_name)<br><span class="muted small">{{ $item->variant_name }}</span>@endif
                    </td>
                    <td class="num">{{ $usd($item->unit_price_usd) }}</td>
                    <td class="num bold">{{ $usd($item->subtotal_usd) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales + monto de este recibo --}}
    <table style="margin-top: 14px;">
        <tr>
            <td style="width: 52%; padding-right: 14px;">
                <div class="paid-box">
                    <div class="label">Monto recibido</div>
                    <div class="amount">{{ $receipt->amount_bs ? $bs($receipt->amount_bs) : $usd($receipt->amount_usd) }}</div>
                    @if($receipt->amount_bs)
                        <div>Equivalente a {{ $usd($receipt->amount_usd) }}</div>
                    @endif
                </div>
                <div style="margin-top: 8px;">
                    @if($balanceDue <= 0)
                        <span class="badge badge-ok">PEDIDO PAGADO EN SU TOTALIDAD</span>
                    @else
                        <span class="badge badge-warn">SALDO PENDIENTE: {{ $usd($balanceDue) }}</span>
                    @endif
                </div>
            </td>
            <td style="width: 48%;">
                <table class="totals">
                    <tr><td class="muted">Subtotal</td><td class="right">{{ $usd($order->subtotal_usd) }}</td></tr>
                    @if((float) $order->shipping_cost_usd > 0)
                        <tr><td class="muted">Envío</td><td class="right">{{ $usd($order->shipping_cost_usd) }}</td></tr>
                    @endif
                    <tr class="grand"><td>Total del pedido</td><td class="right">{{ $usd($order->total_usd) }}</td></tr>
                    @if($receipt->amount_bs && (float) $receipt->exchange_rate > 0)
                        <tr><td class="muted small">Total en Bs (tasa del recibo)</td><td class="right small muted">{{ $bs((float) $order->total_usd * (float) $receipt->exchange_rate) }}</td></tr>
                    @endif
                    <tr><td class="muted">Total pagado</td><td class="right">{{ $usd($amountPaid) }}</td></tr>
                    <tr><td class="bold">Saldo</td><td class="right bold">{{ $usd($balanceDue) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if($receipt->voided_at)
        <div class="void-note">
            <strong>Recibo anulado</strong> el {{ $receipt->voided_at->format('d/m/Y h:i A') }}.
            Motivo: {{ $receipt->void_reason }}
        </div>
    @endif

    {{-- QR de verificación --}}
    <table style="margin-top: 26px; border-top: 1px dashed #d1d5db;">
        <tr>
            <td style="width: 110px; padding-top: 14px;">
                <img src="{{ $qr }}" class="qr" alt="QR">
            </td>
            <td style="padding-top: 22px;">
                <div class="bold" style="font-size: 12px;">Verifica este recibo</div>
                <div class="muted">Escanea el código QR con tu teléfono para confirmar que este recibo es auténtico y ver su estado actual.</div>
                <div class="brand bold" style="margin-top: 10px; font-size: 12px;">¡Gracias por tu compra!</div>
            </td>
        </tr>
    </table>
</body>
</html>
