<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden {{ $orden->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            background: #ffffff;
        }

        .header {
            background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%);
            color: #ffffff;
            padding: 20px 28px 16px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .lab-name {
            font-size: 18pt;
            font-weight: 700;
            color: #ffffff;
        }

        .lab-sub {
            font-size: 7.5pt;
            color: rgba(255,255,255,0.6);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title { text-align: right; }

        .doc-title h2 {
            font-size: 13pt;
            font-weight: 600;
            color: #99f6e4;
        }

        .doc-title .codigo {
            font-size: 9pt;
            color: rgba(255,255,255,0.55);
            font-family: 'Courier New', monospace;
            margin-top: 3px;
        }

        .header-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.15);
            margin: 14px 0 12px;
        }

        .header-bottom {
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            color: rgba(255,255,255,0.6);
        }

        /* Estado badge en header */
        .estado-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.25);
        }

        /* Info grid */
        .info-section {
            padding: 14px 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-grid {
            display: flex;
            gap: 0;
        }

        .info-col {
            flex: 1;
        }

        .info-col + .info-col {
            border-left: 1px solid #e2e8f0;
            padding-left: 20px;
            margin-left: 20px;
        }

        .section-label {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .field-row {
            display: flex;
            margin-bottom: 4px;
            align-items: baseline;
        }

        .field-label {
            font-size: 7.5pt;
            color: #64748b;
            width: 100px;
            flex-shrink: 0;
        }

        .field-value {
            font-size: 8pt;
            font-weight: 600;
            color: #1e293b;
        }

        /* Tabla exámenes */
        .examenes-section {
            padding: 16px 28px;
        }

        .examenes-title {
            font-size: 10pt;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #0f766e;
        }

        table.examenes {
            width: 100%;
            border-collapse: collapse;
        }

        table.examenes thead tr {
            background: #0f766e;
            color: #ffffff;
        }

        table.examenes thead th {
            padding: 7px 10px;
            font-size: 7.5pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }

        table.examenes thead th.right { text-align: right; }

        table.examenes tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        table.examenes tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        table.examenes tbody td {
            padding: 8px 10px;
        }

        .exam-name {
            font-size: 8.5pt;
            font-weight: 600;
            color: #1e293b;
        }

        .exam-meta {
            font-size: 7pt;
            color: #94a3b8;
            margin-top: 1px;
            font-family: 'Courier New', monospace;
        }

        .precio {
            font-size: 9pt;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }

        /* Totales */
        .totales-section {
            padding: 0 28px 14px;
        }

        table.totales {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
        }

        table.totales td {
            padding: 4px 10px;
            font-size: 8.5pt;
        }

        table.totales .total-row td {
            font-size: 11pt;
            font-weight: 700;
            color: #0f766e;
            border-top: 2px solid #0f766e;
            padding-top: 8px;
        }

        table.totales td:last-child {
            text-align: right;
        }

        /* Observaciones */
        .obs-section {
            margin: 0 28px 14px;
            padding: 10px 14px;
            background: #f8fafc;
            border-left: 3px solid #0f766e;
            border-radius: 0 8px 8px 0;
        }

        .obs-label {
            font-size: 7.5pt;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .obs-text {
            font-size: 8pt;
            color: #475569;
            line-height: 1.5;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-left {
            flex: 1;
        }

        .recepcion-info {
            font-size: 7.5pt;
            color: #64748b;
            line-height: 1.6;
        }

        .recepcion-nombre {
            font-weight: 600;
            color: #1e293b;
        }

        .footer-center {
            flex: 1;
            text-align: center;
        }

        .aviso {
            font-size: 6.5pt;
            color: #94a3b8;
            line-height: 1.5;
            max-width: 200px;
            margin: 0 auto;
        }

        .footer-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .qr-label {
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: center;
        }

        .page-number {
            position: fixed;
            bottom: 8px;
            right: 28px;
            font-size: 7pt;
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-top">
            <div>
                <div class="lab-name">BioLab</div>
                <div class="lab-sub">Laboratorio Clínico · Análisis Especializados</div>
            </div>
            <div class="doc-title">
                <h2>Orden de Trabajo</h2>
                <div class="codigo">{{ $orden->codigo }}</div>
            </div>
        </div>
        <hr class="header-divider"/>
        <div class="header-bottom">
            <span>Emitida: {{ $orden->created_at->format('d/m/Y H:i') }}</span>
            <span class="estado-badge">
                {{ ['pendiente'=>'Pendiente','en_proceso'=>'En proceso','listo'=>'Listo','entregado'=>'Entregado','anulado'=>'Anulado'][$orden->estado] ?? $orden->estado }}
            </span>
            <span>Impreso: {{ $fechaImpresion }}</span>
        </div>
    </div>

    <!-- Info paciente + orden -->
    <div class="info-section">
        <div class="info-grid">
            <!-- Paciente -->
            <div class="info-col">
                <div class="section-label">Paciente</div>
                <div class="field-row">
                    <span class="field-label">Nombre:</span>
                    <span class="field-value">{{ $orden->paciente->apellidos }}, {{ $orden->paciente->nombres }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Cédula:</span>
                    <span class="field-value">{{ $orden->paciente->tipo_documento }}-{{ $orden->paciente->cedula }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Edad / Sexo:</span>
                    <span class="field-value">{{ $orden->paciente->edad }} años · {{ $orden->paciente->sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Teléfono:</span>
                    <span class="field-value">{{ $orden->paciente->telefono ?? '—' }}</span>
                </div>
            </div>

            <!-- Orden -->
            <div class="info-col">
                <div class="section-label">Datos de la Orden</div>
                <div class="field-row">
                    <span class="field-label">Recepcionista:</span>
                    <span class="field-value">{{ $orden->recepcionista?->name ?? '—' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Médico ref.:</span>
                    <span class="field-value">{{ $orden->medico?->nombre_completo ?? 'Sin médico referente' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Estado pago:</span>
                    <span class="field-value">{{ ['pendiente'=>'Pendiente','pagado'=>'Pagado','exonerado'=>'Exonerado'][$orden->estado_pago] ?? $orden->estado_pago }}</span>
                </div>
                @if($orden->fecha_entrega_estimada)
                <div class="field-row">
                    <span class="field-label">Entrega est.:</span>
                    <span class="field-value">{{ $orden->fecha_entrega_estimada->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Exámenes -->
    <div class="examenes-section">
        <div class="examenes-title">Exámenes Solicitados</div>
        <table class="examenes">
            <thead>
                <tr>
                    <th style="width:45%">Examen</th>
                    <th style="width:20%">Muestra</th>
                    <th style="width:20%">Tubo</th>
                    <th class="right" style="width:15%">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orden->items as $item)
                <tr>
                    <td>
                        <div class="exam-name">{{ $item->examen->nombre }}</div>
                        <div class="exam-meta">{{ $item->examen->codigo }}</div>
                    </td>
                    <td style="font-size:8pt;color:#475569;">{{ $item->examen->muestra ?? '—' }}</td>
                    <td style="font-size:8pt;color:#475569;">{{ $item->examen->tubo ?? '—' }}</td>
                    <td class="precio">${{ number_format($item->precio, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totales -->
    <div class="totales-section">
        <table class="totales">
            <tr>
                <td style="color:#64748b;">Subtotal</td>
                <td style="color:#475569;">${{ number_format($orden->subtotal, 2) }}</td>
            </tr>
            @if($orden->descuento > 0)
            <tr>
                <td style="color:#64748b;">Descuento</td>
                <td style="color:#dc2626;">-${{ number_format($orden->descuento, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total</td>
                <td>${{ number_format($orden->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Observaciones -->
    @if($orden->observaciones)
    <div class="obs-section">
        <div class="obs-label">Observaciones</div>
        <div class="obs-text">{{ $orden->observaciones }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            <div class="recepcion-info">
                Recibido por:<br>
                <span class="recepcion-nombre">{{ $orden->recepcionista?->name ?? '—' }}</span><br>
                {{ $orden->created_at->format('d/m/Y \a \l\a\s H:i') }}
            </div>
        </div>

        <div class="footer-center">
            <div class="aviso">
                Este documento es una orden de trabajo interna.<br>
                No es válido como comprobante de pago.<br>
                Escanee el QR para verificar la orden.
            </div>
        </div>

        <div class="footer-right">
            <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="90" height="90" alt="QR"/>
            <div class="qr-label">Verificar orden</div>
        </div>
    </div>

    <div class="page-number">
        BioLab · Sistema de Gestión · {{ $fechaImpresion }}
    </div>

</body>
</html>