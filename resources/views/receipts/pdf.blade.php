<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $receipt->formatted_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
        }
        .header p {
            margin: 4px 0 0;
            color: #6b7280;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info td {
            padding: 6px 0;
            vertical-align: top;
        }
        table.info td.label {
            color: #6b7280;
            width: 160px;
        }
        .total-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 16px;
            margin-top: 20px;
        }
        .total-box .amount {
            font-size: 22px;
            font-weight: bold;
        }
        .watermark {
            position: fixed;
            top: 260px;
            left: 60px;
            font-size: 90px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-25deg);
            z-index: 10;
        }
        .void-note {
            margin-top: 24px;
            border: 1px solid #fca5a5;
            background: #fef2f2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    @if($receipt->voided_at)
        <div class="watermark">ANULADO</div>
    @endif

    <div class="header">
        <h1>Recibo {{ $receipt->formatted_number }}</h1>
        <p>Emitido el {{ $receipt->issued_at->format('d/m/Y H:i') }}</p>
    </div>

    <table class="info">
        <tr>
            <td class="label">Cliente</td>
            <td>{{ $receipt->customer_name }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $receipt->customer_email }}</td>
        </tr>
        @if($receipt->customer_cedula_rif)
        <tr>
            <td class="label">Cédula/RIF</td>
            <td>{{ $receipt->customer_cedula_rif }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Pedido</td>
            <td>#{{ $receipt->order_id }}</td>
        </tr>
        <tr>
            <td class="label">Método de pago</td>
            <td>{{ $paymentLabels[$receipt->payment_method] ?? $receipt->payment_method }}</td>
        </tr>
        @if($receipt->reference)
        <tr>
            <td class="label">Referencia</td>
            <td>{{ $receipt->reference }}</td>
        </tr>
        @endif
    </table>

    <div class="total-box">
        <div class="amount">${{ number_format((float) $receipt->amount_usd, 2) }}</div>
        @if($receipt->amount_bs)
            <div>{{ number_format((float) $receipt->amount_bs, 2) }} Bs (tasa {{ number_format((float) $receipt->exchange_rate, 4) }})</div>
        @endif
    </div>

    @if($receipt->voided_at)
        <div class="void-note">
            Recibo anulado el {{ $receipt->voided_at->format('d/m/Y H:i') }}.
            Motivo: {{ $receipt->void_reason }}
        </div>
    @endif
</body>
</html>
