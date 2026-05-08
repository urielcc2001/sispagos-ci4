<?php

namespace App\Models;

class AdeudoModel
{
    private static array $meses = [
        1  => 'Enero',    2  => 'Febrero',   3  => 'Marzo',
        4  => 'Abril',    5  => 'Mayo',       6  => 'Junio',
        7  => 'Julio',    8  => 'Agosto',     9  => 'Septiembre',
        10 => 'Octubre',  11 => 'Noviembre',  12 => 'Diciembre',
    ];

    // ── Inscripción ─────────────────────────────────────────────────

    public function getInscripcion(string $numControl, string $nivel): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'inscripcion')
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    // ── Ancla: mes/año real de inicio del ciclo ─────────────────────
    //   Si la inscripción tiene mes_inicio_ciclo, ese es el punto de partida.
    //   Si ese mes es mayor que el mes real de inscripción, el año retrocede uno.

    private function getAnclaInscripcion(array $inscripcion): array
    {
        $mesReal = (int) date('n', strtotime($inscripcion['created_at']));
        $anio    = (int) date('Y', strtotime($inscripcion['created_at']));
        $mes     = ! empty($inscripcion['mes_inicio_ciclo'])
                   ? (int) $inscripcion['mes_inicio_ciclo']
                   : $mesReal;
        if ($mes > $mesReal) {
            $anio--;
        }
        return ['anio' => $anio, 'mes' => $mes];
    }

    // ── Ancla por año específico: reinscripción del año → inscripción del año → directa ─

    public function getAnclaParaAnio(string $numControl, string $nivel, int $anio): array
    {
        $db = \Config\Database::connect();

        $pago = $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'reinscripcion')
            ->where('YEAR(created_at)', $anio)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (! $pago) {
            $pago = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'inscripcion')
                ->where('YEAR(created_at)', $anio)
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()->getRowArray();
        }

        if (! $pago) {
            return ['directa' => true, 'mes' => null, 'anio' => $anio, 'pago' => null];
        }

        $mesReal   = (int) date('n', strtotime($pago['created_at']));
        $anioAncla = (int) date('Y', strtotime($pago['created_at']));
        $mesAncla  = ! empty($pago['mes_inicio_ciclo'])
                     ? (int) $pago['mes_inicio_ciclo']
                     : $mesReal;
        if ($mesAncla > $mesReal) {
            $anioAncla--;
        }

        return ['directa' => false, 'mes' => $mesAncla, 'anio' => $anioAncla, 'pago' => $pago];
    }

    // ── Mensualidades pagadas (como claves "YYYY-MM") ────────────────
    // Usa anio_mensualidad cuando está disponible; si no, cae en YEAR(fecha_pago_real).

    public function getPagadosYearMonth(string $numControl, string $nivel): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('pagos')
            ->select('COALESCE(anio_mensualidad, YEAR(fecha_pago_real)) AS anio, periodo_pago AS mes')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->where('(fecha_pago_real IS NOT NULL OR anio_mensualidad IS NOT NULL)', null, false)
            ->get()
            ->getResultArray();

        return array_map(
            fn($r) => $r['anio'] . '-' . str_pad($r['mes'], 2, '0', STR_PAD_LEFT),
            $rows
        );
    }

    // ── Mensualidades pagadas con folio (mapa "YYYY-MM" → ['folio', 'tiene_completo']) ─
    // tiene_completo = true si existe al menos un registro sin num_abono (pago total).
    // tiene_completo = false = solo abonos parciales registrados para ese mes.

    private function getPagadosData(string $numControl, string $nivel): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('pagos')
            ->select('
                MAX(folio_digital) AS folio_digital,
                COALESCE(anio_mensualidad, YEAR(fecha_pago_real)) AS anio,
                periodo_pago AS mes,
                MAX(num_abono IS NULL) AS tiene_completo,
                SUM(num_abono IS NOT NULL) AS num_abonos_pagados
            ')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->where('(fecha_pago_real IS NOT NULL OR anio_mensualidad IS NOT NULL)', null, false)
            ->groupBy('anio, mes')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $key       = $r['anio'] . '-' . str_pad($r['mes'], 2, '0', STR_PAD_LEFT);
            $map[$key] = [
                'folio'          => $r['folio_digital'],
                'tiene_completo' => (bool)(int)$r['tiene_completo'],
                'num_abonos'     => (int)$r['num_abonos_pagados'],
            ];
        }
        return $map;
    }

    // ── Estado de cuenta: 12 meses de un año con status ─────────────
    // Devuelve todos los registros individuales de mensualidad de un año,
    // indexados por clave "YYYY-MM", para mostrar detalle de abonos en modal.
    public function getPagosDetallePorMes(string $numControl, string $nivel, int $anio): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('pagos')
            ->select('folio_digital, monto, num_abono, created_at, fecha_pago_real, periodo_pago, anio_mensualidad, metodo_pago')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->where("COALESCE(anio_mensualidad, YEAR(COALESCE(fecha_pago_real, created_at))) = {$anio}", null, false)
            ->orderBy('periodo_pago', 'ASC')
            ->orderBy('num_abono', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $anioR = $r['anio_mensualidad']
                ?? date('Y', strtotime($r['fecha_pago_real'] ?? $r['created_at']));
            $key = $anioR . '-' . str_pad((int) $r['periodo_pago'], 2, '0', STR_PAD_LEFT);
            $map[$key][] = [
                'folio'        => $r['folio_digital'],
                'monto'        => (float) $r['monto'],
                'num_abono'    => $r['num_abono'] !== null ? (int) $r['num_abono'] : null,
                'fecha'        => substr($r['fecha_pago_real'] ?? $r['created_at'], 0, 10),
                'metodo_pago'  => $r['metodo_pago'] ?? 'Efectivo',
            ];
        }
        return $map;
    }

    //   'pagado' | 'pendiente' | 'futuro' | 'na' (fuera del ciclo o sin historial)

    public function getEstadoCuentaMensual(string $numControl, string $nivel, int $anio): array
    {
        $pagadosData = $this->getPagadosData($numControl, $nivel);
        $anioHoy     = (int) date('Y');
        $mesHoy      = (int) date('n');

        // Ancla específica del año consultado (reinscripción → inscripción → directa)
        $ancla    = $this->getAnclaParaAnio($numControl, $nivel, $anio);
        $directa  = $ancla['directa'];
        $inscAnio = $ancla['anio'];
        $inscMes  = $ancla['mes'];

        // Modo directa: ancla dinámica = primer mes con pago registrado ese año.
        // Meses anteriores a ese primer pago → 'na' (no sabemos si se liquidaron fuera del sistema).
        // Si no hay ningún pago ese año → todo el pasado es 'na' (sin deuda confirmada).
        $primerMesPagado = null;
        if ($directa) {
            foreach ($pagadosData as $key => $_) {
                [$anioKey, $mesKey] = explode('-', $key);
                if ((int) $anioKey === $anio) {
                    $mesNum = (int) $mesKey;
                    if ($primerMesPagado === null || $mesNum < $primerMesPagado) {
                        $primerMesPagado = $mesNum;
                    }
                }
            }
        }

        $estado = [];
        for ($mes = 1; $mes <= 12; $mes++) {

            // Modo con ancla: meses anteriores al inicio del ciclo → na
            if (! $directa && ($anio < $inscAnio || ($anio === $inscAnio && $inscMes !== null && $mes < $inscMes))) {
                $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na', 'folio_digital' => null, 'abonos' => 0];
                continue;
            }

            // Modo directa: meses antes del primer pago del año → na
            if ($directa && $primerMesPagado !== null && $mes < $primerMesPagado) {
                $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na', 'folio_digital' => null, 'abonos' => 0];
                continue;
            }

            // Modo directa sin ningún pago ese año → pasado = na, futuro = futuro
            if ($directa && $primerMesPagado === null) {
                $esPasado = ($anio < $anioHoy) || ($anio === $anioHoy && $mes <= $mesHoy);
                $estado[] = [
                    'mes'           => $mes,
                    'nombre'        => self::$meses[$mes],
                    'status'        => $esPasado ? 'na' : 'futuro',
                    'folio_digital' => null,
                    'abonos'        => 0,
                ];
                continue;
            }

            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

            if (isset($pagadosData[$key])) {
                $data         = $pagadosData[$key];
                $status       = $data['tiene_completo'] ? 'pagado' : 'parcial';
                $folioDigital = $data['folio'];
                $numAbonos    = $data['tiene_completo'] ? 0 : $data['num_abonos'];
            } elseif ($anio < $anioHoy || ($anio === $anioHoy && $mes <= $mesHoy)) {
                $status       = 'pendiente';
                $folioDigital = null;
                $numAbonos    = 0;
            } else {
                $status       = 'futuro';
                $folioDigital = null;
                $numAbonos    = 0;
            }

            $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => $status, 'folio_digital' => $folioDigital, 'abonos' => $numAbonos];
        }

        return $estado;
    }

    // ── Adeudos: gaps desde la inscripción hasta hoy ─────────────────

    public function getAdeudosMensualidadDesdeInscripcion(string $numControl, string $nivel): array
    {
        $inscripcion = $this->getInscripcion($numControl, $nivel);
        if (! $inscripcion) {
            return [];
        }

        $pagados  = $this->getPagadosYearMonth($numControl, $nivel);
        $ancla    = $this->getAnclaInscripcion($inscripcion);
        $anioInsc = $ancla['anio'];
        $mesInsc  = $ancla['mes'];
        $anioHoy  = (int) date('Y');
        $mesHoy   = (int) date('n');

        $adeudos = [];
        $anio    = $anioInsc;
        $mes     = $mesInsc;

        while ($anio < $anioHoy || ($anio === $anioHoy && $mes <= $mesHoy)) {
            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);
            if (! in_array($key, $pagados, true)) {
                $adeudos[] = self::$meses[$mes] . ' ' . $anio;
            }
            $mes++;
            if ($mes > 12) { $mes = 1; $anio++; }
        }

        return $adeudos;
    }

    // Alias para el endpoint AJAX (ventanilla de cobro)
    public function getAdeudosParaAlerta(string $numControl, string $nivel): array
    {
        return $this->getAdeudosMensualidadDesdeInscripcion($numControl, $nivel);
    }

    // ── Años disponibles en el selector de estado de cuenta ──────────
    //   Incluye años con cualquier pago, aunque no haya inscripción formal.

    public function getAniosConPagos(string $numControl, string $nivel): array
    {
        $db      = \Config\Database::connect();
        $anioHoy = (int) date('Y');

        $inscripcion = $this->getInscripcion($numControl, $nivel);
        $anioInicio  = $inscripcion ? $this->getAnclaInscripcion($inscripcion)['anio'] : $anioHoy;

        $minPago = $db->table('pagos')
            ->select('MIN(YEAR(created_at)) AS min_anio')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->get()->getRowArray()['min_anio'];

        if ($minPago) {
            $anioInicio = min($anioInicio, (int) $minPago);
        }

        $anios = [];
        for ($y = $anioHoy; $y >= $anioInicio; $y--) {
            $anios[] = (string) $y;
        }

        return $anios;
    }

    // ── Info del alumno en el sistema ────────────────────────────────

    public function getInfoAlumno(string $numControl, string $nivel): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('pagos')
            ->select('num_control, nivel, nombre_alumno, carrera, modalidad')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    // ── Inscripciones / Reinscripciones del alumno ──────────────────

    public function getInscripciones(string $numControl, string $nivel): array
    {
        $db = \Config\Database::connect();
        return $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->whereIn('concepto', ['inscripcion', 'reinscripcion'])
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();
    }

    // ── Pagos que no son mensualidad ni inscripción (trámites, etc.) ─

    public function getPagosOtros(string $numControl, string $nivel): array
    {
        $db = \Config\Database::connect();
        return $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->whereNotIn('concepto', ['inscripcion', 'reinscripcion', 'mensualidad'])
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();
    }

    // ── Totales por tipo de concepto ─────────────────────────────────

    public function getTotalesPagado(string $numControl, string $nivel): array
    {
        $db = \Config\Database::connect();

        $tInsc = (float) ($db->table('pagos')
            ->selectSum('monto', 'total')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->whereIn('concepto', ['inscripcion', 'reinscripcion'])
            ->get()->getRowArray()['total'] ?? 0);

        $tMens = (float) ($db->table('pagos')
            ->selectSum('monto', 'total')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->get()->getRowArray()['total'] ?? 0);

        $cMens = (int) $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->countAllResults();

        $tOtros = (float) ($db->table('pagos')
            ->selectSum('monto', 'total')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->whereNotIn('concepto', ['inscripcion', 'reinscripcion', 'mensualidad'])
            ->get()->getRowArray()['total'] ?? 0);

        return [
            'inscripciones'      => $tInsc,
            'mensualidades'      => $tMens,
            'mensualidades_cnt'  => $cMens,
            'otros'              => $tOtros,
            'total'              => $tInsc + $tMens + $tOtros,
        ];
    }

    // ── Estado de meses para ventanilla de cobro ─────────────────────────
    //   Acepta un año específico (0 = año actual) para cobrar adeudos pasados.
    //   Ancla: reinscripción del año → inscripción del año → modo directo.
    //   Devuelve status: 'pagado' | 'parcial' | 'pendiente' | 'futuro' | 'na'

    public function getEstadoMensualParaCobro(string $numControl, string $nivel, int $anio = 0): array
    {
        $anioHoy = (int) date('Y');
        $mesHoy  = (int) date('n');

        if ($anio === 0) {
            $anio = $anioHoy;
        }

        $db = \Config\Database::connect();

        // 1. Reinscripción del año solicitado (ancla más específica)
        $pagoInicial = $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'reinscripcion')
            ->where('YEAR(created_at)', $anio)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        // 2. Inscripción del año solicitado (respaldo)
        if (! $pagoInicial) {
            $pagoInicial = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'inscripcion')
                ->where('YEAR(created_at)', $anio)
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()->getRowArray();
        }

        $directa   = ! $pagoInicial;
        $mesAncla  = null;
        $anioAncla = $anio;

        if (! $directa) {
            $mesReal   = (int) date('n', strtotime($pagoInicial['created_at']));
            $anioAncla = (int) date('Y', strtotime($pagoInicial['created_at']));
            $mesAncla  = ! empty($pagoInicial['mes_inicio_ciclo'])
                         ? (int) $pagoInicial['mes_inicio_ciclo']
                         : $mesReal;
            if ($mesAncla > $mesReal) {
                $anioAncla--;
            }
        }

        $pagadosData = $this->getPagadosData($numControl, $nivel);
        $detalleMap  = $this->getPagosDetallePorMes($numControl, $nivel, $anio);
        $meses       = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            // Meses anteriores al ancla del ciclo → no aplica
            if (! $directa && ($anio < $anioAncla || ($anio === $anioAncla && $mesAncla !== null && $mes < $mesAncla))) {
                $meses[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na', 'folio_digital' => null, 'abonos' => 0, 'abonos_detalle' => []];
                continue;
            }

            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

            if (isset($pagadosData[$key])) {
                // 'pagado' = tiene al menos un pago completo; 'parcial' = solo abonos
                $status     = $pagadosData[$key]['tiene_completo'] ? 'pagado' : 'parcial';
                $folio      = $pagadosData[$key]['folio'];
                $numAbonos  = $pagadosData[$key]['tiene_completo'] ? 0 : $pagadosData[$key]['num_abonos'];
                $detalle    = $status === 'parcial' ? ($detalleMap[$key] ?? []) : [];
            } elseif ($anio < $anioHoy || ($anio === $anioHoy && $mes <= $mesHoy)) {
                $status    = 'pendiente';
                $folio     = null;
                $numAbonos = 0;
                $detalle   = [];
            } else {
                $status    = 'futuro';
                $folio     = null;
                $numAbonos = 0;
                $detalle   = [];
            }

            $meses[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => $status, 'folio_digital' => $folio, 'abonos' => $numAbonos, 'abonos_detalle' => $detalle];
        }

        return [
            'meses'     => $meses,
            'directa'   => $directa,
            'mes_ancla' => $mesAncla,
        ];
    }

    // ── Reporte de morosos (híbrido: meses para uni/prepa, materias para posgrado) ──

    public function getMorosos(?string $nivel = null): array
    {
        $morosos = [];

        // Posgrado: detección por materias del cuatrimestre actual
        if ($nivel === null || $nivel === 'posgrado') {
            $morosos = $this->getMorososPosgrado();
        }

        // Licenciatura / Prepa: detección por meses desde inscripción
        if ($nivel !== 'posgrado') {
            $db    = \Config\Database::connect();
            $query = $db->table('pagos')
                ->select('num_control, nivel, MAX(nombre_alumno) AS nombre_alumno, MAX(carrera) AS carrera')
                ->where('concepto', 'inscripcion');

            if ($nivel) {
                $query->where('nivel', $nivel);
            } else {
                $query->whereIn('nivel', ['uni', 'prepa']);
            }

            $alumnos = $query->groupBy('num_control, nivel')->get()->getResultArray();

            foreach ($alumnos as $alumno) {
                $adeudos = $this->getAdeudosMensualidadDesdeInscripcion(
                    $alumno['num_control'],
                    $alumno['nivel']
                );
                if (! empty($adeudos)) {
                    $morosos[] = [
                        'num_control'   => $alumno['num_control'],
                        'nivel'         => $alumno['nivel'],
                        'nombre_alumno' => $alumno['nombre_alumno'],
                        'carrera'       => $alumno['carrera'] ?? '—',
                        'adeudos'       => $adeudos,
                        'total_adeudos' => count($adeudos),
                    ];
                }
            }
        }

        usort($morosos, fn($a, $b) => $b['total_adeudos'] <=> $a['total_adeudos']);

        return $morosos;
    }

    // ── Morosos de Posgrado: materias sin pagar del cuatrimestre actual ──

    private function getMorososPosgrado(): array
    {
        $db    = \Config\Database::connect();
        $dbUni = \Config\Database::connect('uni');

        // Todos los alumnos de posgrado con al menos un pago registrado
        $alumnos = $db->table('pagos')
            ->select('num_control, MAX(nombre_alumno) AS nombre_alumno, MAX(carrera) AS carrera, MAX(modalidad) AS modalidad')
            ->where('nivel', 'posgrado')
            ->groupBy('num_control')
            ->get()->getResultArray();

        $morosos = [];

        foreach ($alumnos as $alumno) {
            $numControl = $alumno['num_control'];

            // Obtener clavelicen y cuatrimestre actual desde la DB académica
            $row = $dbUni->table('alumnos_datos_personales adp')
                ->select('lic.id AS clavelicen, gm.cuatrisem')
                ->join('grupos_modalidad gm', 'gm.id_grupos = adp.id_grupo', 'left')
                ->join('licenciaturas lic', 'lic.id = gm.licenciatura', 'left')
                ->where('adp.numero_control', $numControl)
                ->get()->getRowArray();

            if (empty($row['clavelicen'])) {
                continue;
            }

            // Materias del cuatrimestre actual del alumno
            $matQ = $dbUni->table('materias')
                ->select('materia, clavemateria')
                ->where('clavelicen', $row['clavelicen']);

            if (! empty($row['cuatrisem'])) {
                $matQ->where('cuatrimestre', $row['cuatrisem']);
            }

            $materias = $matQ->orderBy('id', 'ASC')->get()->getResultArray();

            if (empty($materias)) {
                continue;
            }

            // Materias ya pagadas por este alumno
            $matNombres = array_column($materias, 'materia');
            $pagadas    = $db->table('pagos')
                ->select('detalle_tramite')
                ->where('num_control', $numControl)
                ->where('nivel', 'posgrado')
                ->where('concepto', 'mensualidad')
                ->whereIn('detalle_tramite', $matNombres)
                ->get()->getResultArray();

            $pagadasSet = array_fill_keys(array_column($pagadas, 'detalle_tramite'), true);

            $prefix  = mb_stripos($alumno['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
            $adeudos = [];

            foreach ($materias as $mat) {
                if (! isset($pagadasSet[$mat['materia']])) {
                    $adeudos[] = $prefix . ' — ' . $mat['materia'];
                }
            }

            if (! empty($adeudos)) {
                $morosos[] = [
                    'num_control'   => $numControl,
                    'nivel'         => 'posgrado',
                    'nombre_alumno' => $alumno['nombre_alumno'],
                    'carrera'       => $alumno['carrera'] ?? '—',
                    'adeudos'       => $adeudos,
                    'total_adeudos' => count($adeudos),
                ];
            }
        }

        usort($morosos, fn($a, $b) => $b['total_adeudos'] <=> $a['total_adeudos']);

        return $morosos;
    }
}
