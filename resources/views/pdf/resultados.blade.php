<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Resultados — {{ $orden->codigo }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:7pt; color:#1e293b; background:#fff; }

        /* HEADER */
        .hbar1 { background:#0f766e; height:4px; }
        .hbar2 { background:#134e4a; height:2px; }
        table.htop { width:100%; border-collapse:collapse; padding:12px 28px; }
        table.htop td { vertical-align:top; padding:12px 28px 10px; }
        .logo-name { font-size:18pt; font-weight:700; color:#0f172a; letter-spacing:-1px; line-height:1; }
        .logo-name span { color:#0f766e; }
        .logo-tag  { font-size:5.5pt; color:#64748b; text-transform:uppercase; letter-spacing:2px; margin-top:3px; }
        .logo-cont { font-size:6pt; color:#94a3b8; margin-top:5px; line-height:1.7; }
        .doc-tipo  { font-size:12pt; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#0f766e; }
        .doc-cod   { font-size:10pt; font-weight:700; font-family:'Courier New',monospace; letter-spacing:2px; color:#0f172a; margin-top:3px; }
        .doc-fecha { font-size:6pt; color:#94a3b8; margin-top:3px; }
        .doc-est   { display:inline-block; padding:2px 10px; font-size:6pt; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; margin-top:4px; }
        .est-listo     { background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4; }
        .est-entregado { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }

        /* PACIENTE */
        .pac-wrap { background:#f8fafc; border-bottom:1px solid #e2e8f0; border-top:1px solid #e2e8f0; padding:9px 28px; }
        .strip { font-size:5.5pt; font-weight:700; text-transform:uppercase; letter-spacing:2px; color:#0f766e; border-left:3px solid #0f766e; padding-left:7px; margin-bottom:7px; }
        table.pac { width:100%; border-collapse:collapse; }
        table.pac td { padding:2px 0; vertical-align:top; }
        td.pl { font-size:5.5pt; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#94a3b8; width:95px; }
        td.pv { font-size:7pt; font-weight:600; color:#0f172a; }
        td.pv.mono { font-family:'Courier New',monospace; font-size:6.5pt; }
        td.ps { width:20px; }

        /* MEDICO */
        .med-wrap { background:#0f766e; padding:6px 28px; }
        table.med { width:100%; border-collapse:collapse; }
        .ml { font-size:5.5pt; text-transform:uppercase; letter-spacing:1.5px; color:rgba(255,255,255,.55); font-weight:700; margin-bottom:2px; }
        .mv { font-size:7.5pt; font-weight:600; color:#fff; }
        .me { font-size:6pt; color:rgba(255,255,255,.6); }

        /* ══ RESULTADOS ══
           Layout idéntico a la hoja física:
           - Cada "fila de la hoja" es una <table> de 100% ancho
           - Cada sección (Hematología, Orina, etc.) es una <td> dentro de esa fila
           - Dentro de cada sección, las sub-columnas (Aspecto, Acetona...) son <td> dentro de otra tabla
        */
        .rb { padding:8px 28px 0; }

        /* Fila de secciones */
        table.fila-secciones {
            width:100%; border-collapse:collapse; margin-bottom:5px;
            border:1px solid #cbd5e1;
        }

        /* Celda de sección (ej: "Orina", "Hematología") */
        td.seccion {
            vertical-align:top; border:1px solid #cbd5e1; padding:0;
        }

        /* Título de sección */
        .sec-titulo {
            background:#0f172a; color:#e2e8f0;
            font-size:6pt; font-weight:700; text-transform:uppercase; letter-spacing:2px;
            padding:3px 7px;
        }

        /* Tabla de sub-columnas dentro de la sección */
        table.subcols {
            width:100%; border-collapse:collapse;
        }

        /* Celda de sub-columna (ej: "Aspecto", "Acetona") */
        td.subcol {
            vertical-align:top; padding:0;
            border-right:1px solid #e2e8f0;
        }
        td.subcol:last-child { border-right:none; }

        /* Título de sub-columna */
        .sub-titulo {
            background:#f1f5f9; color:#475569;
            font-size:5.5pt; font-weight:700; text-transform:uppercase; letter-spacing:1px;
            border-bottom:1px solid #e2e8f0;
            padding:2px 7px;
        }

        /* Tabla de ítems dentro de la sub-columna */
        table.items { width:100%; border-collapse:collapse; }
        table.items td {
            padding:2.5px 7px; vertical-align:middle;
            border-bottom:1px solid #f1f5f9; font-size:6.5pt;
        }
        table.items tr:last-child td { border-bottom:none; }
        table.items tr:nth-child(even) td { background:#f8fafc; }

        td.in { color:#0f172a;   }
        td.ir {
            width:30%; text-align:right; 
            font-family:'Courier New',monospace; color:#0f172a; white-space:nowrap;
        }
        td.ir.fuera   { color:#b45309; }
        td.ir.critico { color:#dc2626; }
        .u { font-size:5pt; color:#94a3b8; font-weight:400; }

        /* Fila de referencia debajo del resultado */
        td.iref {
            font-size:5.5pt; color:#94a3b8; text-align:right;
            white-space:nowrap; padding-top:0 !important;
        }

        /* FOOTER */
        .fw { margin-top:12px; border-top:2px solid #0f172a; }
        .fa { background:#0f766e; height:3px; }
        table.ftbl { width:100%; border-collapse:collapse; }
        table.ftbl td { padding:10px 14px; vertical-align:bottom; }
        .fe { height:28px; border-bottom:1px solid #cbd5e1; width:155px; margin-bottom:4px; }
        .fl { font-size:5.5pt; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; font-weight:700; }
        .fn { font-size:7pt; font-weight:600; color:#0f172a; margin-top:2px; }
        .fc { font-size:5.5pt; color:#64748b; margin-top:1px; }
        .ab { border:1px solid #e2e8f0; padding:7px 10px; background:#f8fafc; }
        .at { font-size:5.5pt; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#64748b; margin-bottom:4px; }
        .ax { font-size:5.5pt; color:#94a3b8; line-height:1.7; }
        .qb { border:1px solid #e2e8f0; padding:4px; background:#fff; display:inline-block; }
        .ql { font-size:5.5pt; color:#94a3b8; text-align:right; text-transform:uppercase; letter-spacing:1px; margin-top:3px; }

        .pf { position:fixed; bottom:0; left:0; right:0; background:#f8fafc; border-top:1px solid #e2e8f0; padding:3px 28px; }
        table.pft { width:100%; border-collapse:collapse; }
        table.pft td { font-size:5.5pt; color:#cbd5e1; text-transform:uppercase; letter-spacing:.5px; }

        .wm {
            position:fixed; top:48%; left:50%;
            transform:translate(-50%,-50%) rotate(-30deg);
            font-size:72pt; font-weight:900; color:rgba(15,118,110,.04);
            text-transform:uppercase; letter-spacing:6px; white-space:nowrap;
        }
    </style>
</head>
<body>

@if($orden->estado === 'entregado')<div class="wm">Entregado</div>@endif

{{-- HEADER --}}
<div class="hbar1"></div>
<div class="hbar2"></div>
<table class="htop">
<tr>
    <td>
        <div class="logo-name">Bio<span>cerra</span></div>
        <div class="logo-tag">Laboratorio Clínico · Análisis Especializados</div>
        <div class="logo-cont">
            Av. Bolívar, local S/N, Sector Las Parcelas I · Anaco, Edo. Anzoátegui<br>
            RIF. J-505275674 &nbsp;·&nbsp; contacto@biocerra.com.ve
        </div>
    </td>
    <td style="text-align:right; width:230px">
        <div class="doc-tipo">Informe de Resultados</div>
        <div class="doc-cod">{{ $orden->codigo }}</div>
        <div class="doc-fecha">Emitido el {{ $fechaImpresion }}</div>
        <span class="doc-est {{ $orden->estado === 'entregado' ? 'est-entregado' : 'est-listo' }}">
            {{ $orden->estado === 'entregado' ? 'Entregado' : 'Validado' }}
        </span>
    </td>
</tr>
</table>

{{-- PACIENTE --}}
<div class="pac-wrap">
    <div class="strip">Datos del Paciente</div>
    <table class="pac">
        <tr>
            <td class="pl">Apellidos y Nombres</td>
            <td class="pv">{{ strtoupper($orden->paciente->apellidos) }}, {{ $orden->paciente->nombres }}</td>
            <td class="ps"></td>
            <td class="pl">Fecha de Nacimiento</td>
            <td class="pv">{{ \Carbon\Carbon::parse($orden->paciente->fecha_nacimiento)->format('d/m/Y') }}</td>
            <td class="ps"></td>
            <td class="pl">Sexo</td>
            <td class="pv">{{ $orden->paciente->sexo === 'M' ? 'Masculino' : 'Femenino' }}</td>
        </tr>
        <tr>
            <td class="pl">Cédula de Identidad</td>
            <td class="pv mono">{{ $orden->paciente->tipo_documento }}-{{ number_format($orden->paciente->cedula,0,'','.') }}</td>
            <td class="ps"></td>
            <td class="pl">Edad</td>
            <td class="pv">{{ $orden->paciente->edad }} años</td>
            <td class="ps"></td>
            <td class="pl">Teléfono</td>
            <td class="pv">{{ $orden->paciente->telefono ?? '—' }}</td>
        </tr>
    </table>
</div>

{{-- MÉDICO --}}
<div class="med-wrap">
    <table class="med">
        <tr>
            <td><div class="ml">Médico Referente</div><div class="mv">{{ $orden->medico?->nombre_completo ?? 'Sin médico referente' }}</div></td>
            <td style="text-align:right">@if($orden->medico?->especialidad)<span class="me">{{ $orden->medico->especialidad }}</span>@endif</td>
        </tr>
    </table>
</div>

{{-- ══ RESULTADOS ══
     Agrupamos los ítems por:
       categoria   → define la "sección grande" (Hematología, Orina, etc.)
       subcategoria → define la "sub-columna" con título (Aspecto, Acetona, etc.)
       orden_col   → posición de la sub-columna dentro de la sección
       orden_fila  → posición del ítem dentro de la sub-columna
--}}
<div class="rb">
@php
    // Definimos el orden y agrupamiento de las filas del PDF exactamente como la hoja
    // Cada entrada es una "fila visual" → array de secciones que van lado a lado
    $filasLayout = [
        // Fila 1: Hematología | P. Coagulación
        ['Hematología', 'P. Coagulación'],
        // Fila 2: Orina (ancho completo)
        ['Orina'],
        // Fila 3: Heces (ancho completo)
        ['Heces'],
        // Fila 4: Química Sanguínea (ancho completo)
        ['Química Sanguínea'],
        // Fila 5: Inmunología | Misceláneos
        ['Inmunología', 'Misceláneos'],
    ];

    // Agrupamos items por categoria → subcategoria → orden_fila
    $porCategoria = $items->groupBy('categoria');
@endphp

@foreach($filasLayout as $filaSeccs)
@php
    // Solo renderizar si hay ítems en al menos una sección de esta fila
    $hayItems = collect($filaSeccs)->contains(fn($cat) => $porCategoria->has($cat));
@endphp
@if($hayItems)
<table class="fila-secciones">
    <tr>
    @foreach($filaSeccs as $catNombre)
    @php
        $examsCat = $porCategoria->get($catNombre);
        $anchoPct = count($filaSeccs) === 1 ? '100%' : '50%';
    @endphp
    <td class="seccion" style="width:{{ $anchoPct }}">
        @if($examsCat)
        <div class="sec-titulo">{{ $catNombre }}</div>
        @php
            // Agrupar por subcategoria, ordenar sub-columnas por orden_col
            $subcols = $examsCat
                ->groupBy('subcategoria')
                ->sortBy(fn($g) => $g->first()->orden_col ?? 0);
            $nCols = $subcols->count();
            $colW  = $nCols > 0 ? round(100 / $nCols) : 100;
        @endphp
        <table class="subcols">
            <tr>
            @foreach($subcols as $subNombre => $subItems)
            <td class="subcol" style="width:{{ $colW }}%">
                <div class="sub-titulo">{{ $subNombre }}</div>
                <table class="items">
                @foreach($subItems->sortBy('orden_fila') as $item)
                <tr>
                    <td class="in">{{ $item->nombre }}</td>
                    <td class="ir {{ $item->fuera_de_rango ? ($item->indicador === 'critico' ? 'critico' : 'fuera') : '' }}">
                        @if($item->resultado_valor !== null)
                            {{ $item->resultado_valor }}@if($item->unidad)<span class="u"> {{ $item->unidad }}</span>@endif
                        @else
                            {{ $item->resultado_texto ?? '' }}
                        @endif
                    </td>
                </tr>
                @if($item->referencia && ($item->referencia->valor_min !== null || $item->referencia->valor_max !== null))
                <tr>
                    <td colspan="2" class="iref">
                        Ref: {{ $item->referencia->valor_min ?? '?' }}–{{ $item->referencia->valor_max ?? '?' }}
                        @if($item->unidad) {{ $item->unidad }}@endif
                    </td>
                </tr>
                @endif
                @endforeach
                </table>
            </td>
            @endforeach
            </tr>
        </table>
        @endif
    </td>
    @endforeach
    </tr>
</table>
@endif
@endforeach
</div>

{{-- FOOTER --}}
<div class="fw">
    <div class="fa"></div>
    <table class="ftbl">
        <tr>
            <td style="padding: 20px">
                <div class="fe"></div>
                <div class="fl">Bioanalista Responsable</div>
                <div class="fn">{{ $items->first()?->procesado_por ?? 'Bioanalista' }}</div>
                <div class="fc">C.B.V. · Colegio de Bioanalistas de Venezuela</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px">
                <div class="ab">
                    <div class="at">Aviso Importante</div>
                    <div class="ax">
                        Este informe es de carácter confidencial y de uso exclusivo del paciente y su médico tratante.
                        Los resultados deben ser interpretados dentro de su contexto clínico particular.
                        Biocerra Laboratorio Clínico no se responsabiliza por el uso indebido de este documento.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="pf">
    <table class="pft"><tr>
        <td>Biocerra · Sistema de Gestión de Laboratorio</td>
        <td style="text-align:center">{{ $orden->codigo }} · Impreso: {{ $fechaImpresion }}</td>
        <td style="text-align:right">Documento generado digitalmente</td>
    </tr></table>
</div>

</body>
</html>