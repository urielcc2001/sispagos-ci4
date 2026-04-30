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

    public function getPagadosYearMonth(string $numControl, string $nivel): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('pagos')
            ->select('YEAR(fecha_pago_real) AS anio, periodo_pago AS mes')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->where('fecha_pago_real IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        return array_map(
            fn($r) => $r['anio'] . '-' . str_pad($r['mes'], 2, '0', STR_PAD_LEFT),
            $rows
        );
    }

    // ── Mensualidades pagadas con folio (mapa "YYYY-MM" → folio_digital) ─

    private function getPagadosData(string $numControl, string $nivel): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('pagos')
            ->select('folio_digital, YEAR(fecha_pago_real) AS anio, periodo_pago AS mes')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'mensualidad')
            ->where('fecha_pago_real IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $key        = $r['anio'] . '-' . str_pad($r['mes'], 2, '0', STR_PAD_LEFT);
            $map[$key]  = $r['folio_digital'];
        }
        return $map;
    }

    // ── Estado de cuenta: 12 meses de un año con status ─────────────
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
                $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na', 'folio_digital' => null];
                continue;
            }

            // Modo directa: meses antes del primer pago del año → na
            if ($directa && $primerMesPagado !== null && $mes < $primerMesPagado) {
                $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na', 'folio_digital' => null];
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
                ];
                continue;
            }

            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

            if (isset($pagadosData[$key])) {
                $status       = 'pagado';
                $folioDigital = $pagadosData[$key];
            } elseif ($anio < $anioHoy || ($anio === $anioHoy && $mes <= $mesHoy)) {
                $status       = 'pendiente';
                $folioDigital = null;
            } else {
                $status       = 'futuro';
                $folioDigital = null;
            }

            $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => $status, 'folio_digital' => $folioDigital];
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

    // ── Estado de meses para ventanilla de cobro (año actual) ──────────
    //   Ancla: reinscripción del año vigente → inscripción del año vigente → modo directo.

    public function getEstadoMensualParaCobro(string $numControl, string $nivel): array
    {
        $anio   = (int) date('Y');
        $mesHoy = (int) date('n');
        $db     = \Config\Database::connect();

        // 1. Reinscripción del año vigente (ancla más específica)
        $pagoInicial = $db->table('pagos')
            ->where('num_control', $numControl)
            ->where('nivel', $nivel)
            ->where('concepto', 'reinscripcion')
            ->where('YEAR(created_at)', $anio)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        // 2. Inscripción del año vigente (respaldo)
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
        $meses       = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            // Meses anteriores al ancla → no aplica para este ciclo
            if (! $directa && ($anio < $anioAncla || ($anio === $anioAncla && $mesAncla !== null && $mes < $mesAncla))) {
                $meses[] = [
                    'mes'           => $mes,
                    'nombre'        => self::$meses[$mes],
                    'status'        => 'na',
                    'folio_digital' => null,
                ];
                continue;
            }

            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

            if (isset($pagadosData[$key])) {
                $status = 'pagado';
                $folio  = $pagadosData[$key];
            } elseif ($mes <= $mesHoy) {
                $status = 'pendiente';
                $folio  = null;
            } else {
                $status = 'futuro';
                $folio  = null;
            }

            $meses[] = [
                'mes'           => $mes,
                'nombre'        => self::$meses[$mes],
                'status'        => $status,
                'folio_digital' => $folio,
            ];
        }

        return [
            'meses'     => $meses,
            'directa'   => $directa,
            'mes_ancla' => $mesAncla,
        ];
    }

    // ── Reporte de morosos ───────────────────────────────────────────

    public function getMorosos(?string $nivel = null): array
    {
        $db = \Config\Database::connect();

        // Tomamos alumnos que tienen inscripcion (ancla correcta)
        $query = $db->table('pagos')
            ->select('num_control, nivel, MAX(nombre_alumno) AS nombre_alumno, MAX(carrera) AS carrera')
            ->where('concepto', 'inscripcion');

        if ($nivel) {
            $query->where('nivel', $nivel);
        }

        $alumnos = $query->groupBy('num_control, nivel')->get()->getResultArray();

        $morosos = [];
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

        usort($morosos, fn($a, $b) => $b['total_adeudos'] <=> $a['total_adeudos']);

        return $morosos;
    }
}
