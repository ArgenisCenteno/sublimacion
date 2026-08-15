<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Examen;
use App\Models\Perfil;
use App\Models\ValorReferencia;
use Illuminate\Support\Facades\DB;

class BiocerraLabSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn() => $this->seedTodo());
    }

    private function seedTodo(): void
    {
        // ══════════════════════════════════════════════════════
        // ESTRUCTURA EXACTA DE LA HOJA FÍSICA DE BIOCERRA
        //
        // categoria  = sección grande (HEMATOLOGÍA, ORINA, etc.)
        // subcategoria = título de columna dentro de la sección
        //               (Aspecto, Acetona, Examen Microscópico...)
        // orden_col  = posición de la columna dentro de la sección (1, 2, 3)
        // orden_fila = posición del ítem dentro de su columna
        // ══════════════════════════════════════════════════════

        $examenes = [

            // ──────────────────────────────────────────────────
            // FILA 1-A: HEMATOLOGÍA
            // ──────────────────────────────────────────────────
            ['codigo'=>'HEM-001','nombre'=>'Hemoglobina',     'nombre_corto'=>'Hgb',   'unidad'=>'gr/%',  'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hematología','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'HEM-002','nombre'=>'Hematocrito',     'nombre_corto'=>'Hto',   'unidad'=>'%',     'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hematología','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'HEM-003','nombre'=>'Glob. Blancos',   'nombre_corto'=>'GB',    'unidad'=>'x mm³', 'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hematología','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'HEM-004','nombre'=>'Glob. Rojos',     'nombre_corto'=>'GR',    'unidad'=>'x mm³', 'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hematología','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'HEM-005','nombre'=>'Plaquetas',       'nombre_corto'=>'Plaq',  'unidad'=>'x mm³', 'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hematología','orden_col'=>1,'orden_fila'=>5],

            // ──────────────────────────────────────────────────
            // FILA 1-B: HEMOGRAMA (diferencial)
            // ──────────────────────────────────────────────────
            ['codigo'=>'HEM-006','nombre'=>'Segmentados',     'nombre_corto'=>'Seg',   'unidad'=>'%',     'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hemograma','orden_col'=>2,'orden_fila'=>1],
            ['codigo'=>'HEM-007','nombre'=>'Linfocitos',      'nombre_corto'=>'Linf',  'unidad'=>'%',     'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hemograma','orden_col'=>2,'orden_fila'=>2],
            ['codigo'=>'HEM-008','nombre'=>'Eosinófilos',     'nombre_corto'=>'Eos',   'unidad'=>'%',     'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hemograma','orden_col'=>2,'orden_fila'=>3],
            ['codigo'=>'HEM-009','nombre'=>'Monocitos',       'nombre_corto'=>'Mono',  'unidad'=>'%',     'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hemograma','orden_col'=>2,'orden_fila'=>4],
            ['codigo'=>'HEM-010','nombre'=>'Imagen Roja',     'nombre_corto'=>'ImgR',  'unidad'=>null,    'muestra'=>'sangre','tubo'=>'EDTA morado','categoria'=>'Hematología','subcategoria'=>'Hemograma','orden_col'=>2,'orden_fila'=>5],

            // ──────────────────────────────────────────────────
            // FILA 1-C: P. COAGULACIÓN
            // ──────────────────────────────────────────────────
            ['codigo'=>'COA-001','nombre'=>'Tp. Protrombina Pac', 'nombre_corto'=>'TP Pac',  'unidad'=>'Seg',  'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'COA-002','nombre'=>'Tp. Protrombina Ctrl','nombre_corto'=>'TP Ctrl', 'unidad'=>'Seg',  'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'COA-003','nombre'=>'Razón',               'nombre_corto'=>'Razón',   'unidad'=>null,   'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'COA-004','nombre'=>'T.P.T. Paciente',     'nombre_corto'=>'TPT Pac', 'unidad'=>'Seg',  'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'COA-005','nombre'=>'T.P.T Ctrl',          'nombre_corto'=>'TPT Ctrl','unidad'=>'Seg',  'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>5],
            ['codigo'=>'COA-006','nombre'=>'Diferencia',          'nombre_corto'=>'Dif',     'unidad'=>null,   'muestra'=>'sangre','tubo'=>'citrato azul','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>1,'orden_fila'=>6],
            ['codigo'=>'COA-007','nombre'=>'VSG',                 'nombre_corto'=>'VSG',     'unidad'=>'mm/h', 'muestra'=>'sangre','tubo'=>'EDTA morado', 'categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>2,'orden_fila'=>1],
            ['codigo'=>'COA-008','nombre'=>'Gpo. Sanguíneo',      'nombre_corto'=>'Gpo Sg',  'unidad'=>null,   'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>2,'orden_fila'=>2],
            ['codigo'=>'COA-009','nombre'=>'Factor Rh',           'nombre_corto'=>'Factor Rh','unidad'=>null,  'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'P. Coagulación','subcategoria'=>'P. Coagulación','orden_col'=>2,'orden_fila'=>3],

            // ──────────────────────────────────────────────────
            // FILA 2: ORINA — 3 sub-columnas
            // ──────────────────────────────────────────────────
            // Col 1: Aspecto
            ['codigo'=>'ORI-001','nombre'=>'Color',           'nombre_corto'=>'Color',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'ORI-002','nombre'=>'Reacción',        'nombre_corto'=>'pH',     'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'ORI-003','nombre'=>'Densidad',        'nombre_corto'=>'Dens',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'ORI-004','nombre'=>'Albúmina',        'nombre_corto'=>'Albúm',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'ORI-005','nombre'=>'Glucosa',         'nombre_corto'=>'Gluc',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>5],
            // Col 2: Acetona
            ['codigo'=>'ORI-006','nombre'=>'Hemoglobina',     'nombre_corto'=>'Hgb O',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Acetona','orden_col'=>2,'orden_fila'=>1],
            ['codigo'=>'ORI-007','nombre'=>'Bilirrubina',     'nombre_corto'=>'Bili O', 'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Acetona','orden_col'=>2,'orden_fila'=>2],
            ['codigo'=>'ORI-008','nombre'=>'Urobilinógeno',   'nombre_corto'=>'Urob',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Acetona','orden_col'=>2,'orden_fila'=>3],
            ['codigo'=>'ORI-009','nombre'=>'Nitritos',        'nombre_corto'=>'Nitr',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Acetona','orden_col'=>2,'orden_fila'=>4],
            // Col 3: Examen Microscópico
            ['codigo'=>'ORI-010','nombre'=>'Leucocitos',      'nombre_corto'=>'Leuco',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>1],
            ['codigo'=>'ORI-011','nombre'=>'Bacterias',       'nombre_corto'=>'Bact',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>2],
            ['codigo'=>'ORI-012','nombre'=>'Hematíes',        'nombre_corto'=>'Hemat',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>3],
            ['codigo'=>'ORI-013','nombre'=>'Células',         'nombre_corto'=>'Cél',    'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>4],
            ['codigo'=>'ORI-014','nombre'=>'Piocitos',        'nombre_corto'=>'Pioc',   'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>5],
            ['codigo'=>'ORI-015','nombre'=>'Cristales',       'nombre_corto'=>'Crist',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>6],
            ['codigo'=>'ORI-016','nombre'=>'Cilindros',       'nombre_corto'=>'Cilin',  'unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>7],
            ['codigo'=>'ORI-017','nombre'=>'Otros',           'nombre_corto'=>'Otros O','unidad'=>null,'muestra'=>'orina','tubo'=>'frasco','categoria'=>'Orina','subcategoria'=>'Examen Microscópico','orden_col'=>3,'orden_fila'=>8],

            // ──────────────────────────────────────────────────
            // FILA 3: HECES — 2 sub-columnas
            // ──────────────────────────────────────────────────
            // Col 1: Aspecto
            ['codigo'=>'HEC-001','nombre'=>'Color',           'nombre_corto'=>'Color H', 'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'HEC-002','nombre'=>'Reacción',        'nombre_corto'=>'Reac H',  'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'HEC-003','nombre'=>'Consistencia',    'nombre_corto'=>'Cons H',  'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'HEC-004','nombre'=>'Sangre',          'nombre_corto'=>'Sg H',    'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'HEC-005','nombre'=>'Moco',            'nombre_corto'=>'Moco H',  'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Aspecto','orden_col'=>1,'orden_fila'=>5],
            // Col 2: Examen Microscópico
            ['codigo'=>'HEC-006','nombre'=>'Concentrado',     'nombre_corto'=>'Conc H',  'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Examen Microscópico','orden_col'=>2,'orden_fila'=>1],
            ['codigo'=>'HEC-007','nombre'=>'Sangre Oculta',   'nombre_corto'=>'Sg Oc',   'unidad'=>null,'muestra'=>'heces','tubo'=>'frasco','categoria'=>'Heces','subcategoria'=>'Examen Microscópico','orden_col'=>2,'orden_fila'=>2],

            // ──────────────────────────────────────────────────
            // FILA 4: QUÍMICA SANGUÍNEA — 2 sub-columnas
            // ──────────────────────────────────────────────────
            // Col 1: Mg/dl
            ['codigo'=>'QS-001','nombre'=>'Glicemia',            'nombre_corto'=>'Glic',    'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'QS-002','nombre'=>'Urea',                'nombre_corto'=>'Urea',    'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'QS-003','nombre'=>'Creatinina',          'nombre_corto'=>'Creat',   'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'QS-004','nombre'=>'Ácido Úrico',         'nombre_corto'=>'Ac Úr',   'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'QS-005','nombre'=>'Colesterol',          'nombre_corto'=>'Colest',  'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>5],
            ['codigo'=>'QS-006','nombre'=>'Triglicéridos',       'nombre_corto'=>'Trigl',   'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>6],
            ['codigo'=>'QS-007','nombre'=>'Calcio',              'nombre_corto'=>'Ca',      'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>7],
            ['codigo'=>'QS-008','nombre'=>'Fósforo',             'nombre_corto'=>'P',       'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>8],
            ['codigo'=>'QS-009','nombre'=>'H.D.L col.',          'nombre_corto'=>'HDL',     'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Mg/dl','orden_col'=>1,'orden_fila'=>9],
            // Col 2: Grs%
            ['codigo'=>'QS-010','nombre'=>'Prot. Totales',       'nombre_corto'=>'Prot T',  'unidad'=>'grs/%','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>1],
            ['codigo'=>'QS-011','nombre'=>'Albúmina',            'nombre_corto'=>'Albúm',   'unidad'=>'grs/%','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>2],
            ['codigo'=>'QS-012','nombre'=>'Globulina',           'nombre_corto'=>'Glob',    'unidad'=>'grs/%','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>3],
            ['codigo'=>'QS-013','nombre'=>'Rel. A/G',            'nombre_corto'=>'A/G',     'unidad'=>null,   'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>4],
            ['codigo'=>'QS-014','nombre'=>'T.G.O',               'nombre_corto'=>'TGO',     'unidad'=>'u/l',  'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>5],
            ['codigo'=>'QS-015','nombre'=>'T.G.P',               'nombre_corto'=>'TGP',     'unidad'=>'u/l',  'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>6],
            ['codigo'=>'QS-016','nombre'=>'Bilirrubina Total',   'nombre_corto'=>'Bili T',  'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>7],
            ['codigo'=>'QS-017','nombre'=>'Bilirrub. Directa',   'nombre_corto'=>'Bili D',  'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>8],
            ['codigo'=>'QS-018','nombre'=>'Bilirrub. Indirecta', 'nombre_corto'=>'Bili I',  'unidad'=>'mg/dl','muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>9],
            ['codigo'=>'QS-019','nombre'=>'Fosf. Alcalina',      'nombre_corto'=>'Fosf A',  'unidad'=>'u/l',  'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>10],
            ['codigo'=>'QS-020','nombre'=>'Amilasa',             'nombre_corto'=>'Amil',    'unidad'=>'u/l',  'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Química Sanguínea','subcategoria'=>'Grs %','orden_col'=>2,'orden_fila'=>11],

            // ──────────────────────────────────────────────────
            // FILA 5-A: INMUNOLOGÍA
            // ──────────────────────────────────────────────────
            ['codigo'=>'INM-001','nombre'=>'V.D.R.L',             'nombre_corto'=>'VDRL',   'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'INM-002','nombre'=>'Proteína C Reactiva', 'nombre_corto'=>'PCR',    'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>2],
            ['codigo'=>'INM-003','nombre'=>'RA Test (Látex)',     'nombre_corto'=>'RA Test','unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>3],
            ['codigo'=>'INM-004','nombre'=>'A.S.T.O',             'nombre_corto'=>'ASTO',   'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>4],
            ['codigo'=>'INM-005','nombre'=>'Monotest',            'nombre_corto'=>'Mono',   'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>5],
            ['codigo'=>'INM-006','nombre'=>'H.I.V',               'nombre_corto'=>'HIV',    'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>6],
            ['codigo'=>'INM-007','nombre'=>'Toxoplasma',          'nombre_corto'=>'Toxo',   'unidad'=>null,'muestra'=>'sangre','tubo'=>'sin anticoag','categoria'=>'Inmunología','subcategoria'=>'Inmunología','orden_col'=>1,'orden_fila'=>7],

            // ──────────────────────────────────────────────────
            // FILA 5-B: MISCELÁNEOS (sin ítems fijos — campo libre)
            // ──────────────────────────────────────────────────
            ['codigo'=>'MIS-001','nombre'=>'Misceláneo 1','nombre_corto'=>'Misc 1','unidad'=>null,'muestra'=>'sangre','tubo'=>null,'categoria'=>'Misceláneos','subcategoria'=>'Misceláneos','orden_col'=>1,'orden_fila'=>1],
            ['codigo'=>'MIS-002','nombre'=>'Misceláneo 2','nombre_corto'=>'Misc 2','unidad'=>null,'muestra'=>'sangre','tubo'=>null,'categoria'=>'Misceláneos','subcategoria'=>'Misceláneos','orden_col'=>1,'orden_fila'=>2],
        ];

        // ── Crear exámenes ──────────────────────────────────
        foreach ($examenes as $data) {
            Examen::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'nombre'        => $data['nombre'],
                    'nombre_corto'  => $data['nombre_corto'],
                    'categoria'     => $data['categoria'],
                    'subcategoria'  => $data['subcategoria'],
                    'orden_col'     => $data['orden_col'],
                    'orden_fila'    => $data['orden_fila'],
                    'muestra'       => $data['muestra'],
                    'tubo'          => $data['tubo'],
                    'unidad'        => $data['unidad'],
                    'precio'        => 0.00,
                    'activo'        => true,
                ]
            );
        }

        // ── Perfiles ────────────────────────────────────────
        $perfiles = [
            ['codigo'=>'PERF-HEM', 'nombre'=>'Hematología Completa',     'categoria'=>'Hematología',      'codigos'=>['HEM-001','HEM-002','HEM-003','HEM-004','HEM-005','HEM-006','HEM-007','HEM-008','HEM-009','HEM-010']],
            ['codigo'=>'PERF-COA', 'nombre'=>'Pruebas de Coagulación',   'categoria'=>'P. Coagulación',   'codigos'=>['COA-001','COA-002','COA-003','COA-004','COA-005','COA-006','COA-007','COA-008','COA-009']],
            ['codigo'=>'PERF-ORI', 'nombre'=>'Examen de Orina Completo', 'categoria'=>'Orina',            'codigos'=>['ORI-001','ORI-002','ORI-003','ORI-004','ORI-005','ORI-006','ORI-007','ORI-008','ORI-009','ORI-010','ORI-011','ORI-012','ORI-013','ORI-014','ORI-015','ORI-016','ORI-017']],
            ['codigo'=>'PERF-HEC', 'nombre'=>'Examen de Heces Completo', 'categoria'=>'Heces',            'codigos'=>['HEC-001','HEC-002','HEC-003','HEC-004','HEC-005','HEC-006','HEC-007']],
            ['codigo'=>'PERF-QS',  'nombre'=>'Química Sanguínea',        'categoria'=>'Química Sanguínea','codigos'=>['QS-001','QS-002','QS-003','QS-004','QS-005','QS-006','QS-007','QS-008','QS-009','QS-010','QS-011','QS-012','QS-013','QS-014','QS-015','QS-016','QS-017','QS-018','QS-019','QS-020']],
            ['codigo'=>'PERF-INM', 'nombre'=>'Panel de Inmunología',     'categoria'=>'Inmunología',      'codigos'=>['INM-001','INM-002','INM-003','INM-004','INM-005','INM-006','INM-007']],
        ];

        foreach ($perfiles as $p) {
            $perfil = Perfil::firstOrCreate(
                ['codigo' => $p['codigo']],
                ['nombre' => $p['nombre'], 'categoria' => $p['categoria'], 'precio' => 0.00, 'activo' => true]
            );
            $ids = Examen::whereIn('codigo', $p['codigos'])->pluck('id');
            $perfil->examenes()->sync($ids);
        }

        // ── Valores de referencia ───────────────────────────
        $refs = [
            'HEM-001' => [
                ['sexo'=>'ambos','edad_min'=>0, 'edad_max'=>0,  'valor_min'=>13.5,'valor_max'=>19.5,'etiqueta'=>'RN'],
                ['sexo'=>'ambos','edad_min'=>1, 'edad_max'=>12, 'valor_min'=>11.5,'valor_max'=>15.5,'etiqueta'=>'Niño'],
                ['sexo'=>'M',    'edad_min'=>13,'edad_max'=>150,'valor_min'=>13.5,'valor_max'=>17.5,'etiqueta'=>'Adulto M'],
                ['sexo'=>'F',    'edad_min'=>13,'edad_max'=>150,'valor_min'=>12.0,'valor_max'=>16.0,'etiqueta'=>'Adulto F'],
            ],
            'HEM-002' => [
                ['sexo'=>'M','edad_min'=>13,'edad_max'=>150,'valor_min'=>40,'valor_max'=>54,'etiqueta'=>'Adulto M'],
                ['sexo'=>'F','edad_min'=>13,'edad_max'=>150,'valor_min'=>36,'valor_max'=>46,'etiqueta'=>'Adulto F'],
            ],
            'HEM-003' => [
                ['sexo'=>'ambos','edad_min'=>0, 'edad_max'=>0,  'valor_min'=>9000, 'valor_max'=>30000,'etiqueta'=>'RN'],
                ['sexo'=>'ambos','edad_min'=>1, 'edad_max'=>12, 'valor_min'=>5000, 'valor_max'=>13000,'etiqueta'=>'Niño'],
                ['sexo'=>'ambos','edad_min'=>13,'edad_max'=>150,'valor_min'=>4500, 'valor_max'=>11000,'etiqueta'=>'Adulto'],
            ],
            'HEM-005' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>150000,'valor_max'=>400000,'etiqueta'=>'Normal']],
            'HEM-006' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>50,'valor_max'=>70,'etiqueta'=>'Seg']],
            'HEM-007' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>20,'valor_max'=>40,'etiqueta'=>'Linf']],
            'HEM-008' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>1, 'valor_max'=>4, 'etiqueta'=>'Eos']],
            'HEM-009' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>2, 'valor_max'=>8, 'etiqueta'=>'Mono']],
            'COA-001' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>11,'valor_max'=>13,'etiqueta'=>'Normal']],
            'COA-004' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>25,'valor_max'=>35,'etiqueta'=>'Normal']],
            'COA-007' => [
                ['sexo'=>'M','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,'valor_max'=>15,'etiqueta'=>'Hombre'],
                ['sexo'=>'F','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,'valor_max'=>20,'etiqueta'=>'Mujer'],
            ],
            'QS-001' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>70,  'valor_max'=>110, 'etiqueta'=>'Normal']],
            'QS-002' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>15,  'valor_max'=>48,  'etiqueta'=>'Normal']],
            'QS-003' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>0.7, 'valor_max'=>1.2, 'etiqueta'=>'Normal']],
            'QS-004' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>3.0, 'valor_max'=>7.0, 'etiqueta'=>'Normal']],
            'QS-005' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>120, 'valor_max'=>220, 'etiqueta'=>'Normal']],
            'QS-006' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>45,  'valor_max'=>150, 'etiqueta'=>'Normal']],
            'QS-007' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>8.7, 'valor_max'=>10.2,'etiqueta'=>'Normal']],
            'QS-008' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>2.5, 'valor_max'=>4.9, 'etiqueta'=>'Normal']],
            'QS-009' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>32,  'valor_max'=>77,  'etiqueta'=>'Normal']],
            'QS-010' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>6.1, 'valor_max'=>7.9, 'etiqueta'=>'Normal']],
            'QS-011' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>3.4, 'valor_max'=>4.8, 'etiqueta'=>'Normal']],
            'QS-012' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>2.0, 'valor_max'=>3.2, 'etiqueta'=>'Normal']],
            'QS-014' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,   'valor_max'=>40,  'etiqueta'=>'Normal']],
            'QS-015' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,   'valor_max'=>40,  'etiqueta'=>'Normal']],
            'QS-016' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,   'valor_max'=>1.3, 'etiqueta'=>'Normal']],
            'QS-017' => [['sexo'=>'ambos','edad_min'=>0,'edad_max'=>150,'valor_min'=>0,   'valor_max'=>0.3, 'etiqueta'=>'Normal']],
        ];

        foreach ($refs as $codigo => $valores) {
            $ex = Examen::where('codigo', $codigo)->first();
            if (!$ex) continue;
            foreach ($valores as $v) {
                ValorReferencia::firstOrCreate(
                    ['examen_id'=>$ex->id,'sexo'=>$v['sexo'],'edad_min'=>$v['edad_min'],'edad_max'=>$v['edad_max']],
                    ['valor_min'=>$v['valor_min']??null,'valor_max'=>$v['valor_max']??null,'valor_texto'=>$v['valor_texto']??null,'etiqueta'=>$v['etiqueta']??null]
                );
            }
        }
    }
}