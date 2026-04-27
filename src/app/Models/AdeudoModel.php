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

    // ── Estado de cuenta: 12 meses de un año con status ─────────────
    //   'pagado' | 'pendiente' | 'futuro' | 'na' (antes de inscripción)

    public function getEstadoCuentaMensual(string $numControl, string $nivel, int $anio): array
    {
        $pagados     = $this->getPagadosYearMonth($numControl, $nivel);
        $inscripcion = $this->getInscripcion($numControl, $nivel);
        $anioHoy     = (int) date('Y');
        $mesHoy      = (int) date('n');

        $inscAnio = $inscripcion ? (int) date('Y', strtotime($inscripcion['created_at'])) : null;
        $inscMes  = $inscripcion ? (int) date('n', strtotime($inscripcion['created_at'])) : null;

        $estado = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            // Meses anteriores a la inscripción → no aplica
            if ($inscAnio !== null) {
                if ($anio < $inscAnio || ($anio === $inscAnio && $mes < $inscMes)) {
                    $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => 'na'];
                    continue;
                }
            }

            $key = $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

            if (in_array($key, $pagados, true)) {
                $status = 'pagado';
            } elseif ($anio < $anioHoy || ($anio === $anioHoy && $mes <= $mesHoy)) {
                $status = 'pendiente';
            } else {
                $status = 'futuro';
            }

            $estado[] = ['mes' => $mes, 'nombre' => self::$meses[$mes], 'status' => $status];
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
        $anioInsc = (int) date('Y', strtotime($inscripcion['created_at']));
        $mesInsc  = (int) date('n', strtotime($inscripcion['created_at']));
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

    public function getAniosConPagos(string $numControl, string $nivel): array
    {
        $inscripcion = $this->getInscripcion($numControl, $nivel);
        $anioHoy     = (int) date('Y');
        $anioInicio  = $inscripcion ? (int) date('Y', strtotime($inscripcion['created_at'])) : $anioHoy;

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
