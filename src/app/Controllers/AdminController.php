<?php

namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class AdminController extends BaseController
{
    private function checkAdmin(): mixed
    {
        $session = service('session');

        if (! $session->get('logged_in') || $session->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        return null;
    }

    private function checkAuth(): mixed
    {
        $session = service('session');

        if (! $session->get('logged_in') || ! in_array($session->get('rol'), ['admin', 'cajero'], true)) {
            return redirect()->to(base_url('auth/login'));
        }

        return null;
    }

    // ── Helper compartido: aplica filtros GET y devuelve pagos ──────
    private function filtrarPagos(): array
    {
        $session    = service('session');
        $request    = service('request');
        $db         = \Config\Database::connect();
        $rol        = $session->get('rol');
        $idSesion   = (int) $session->get('id_usuario');

        $fechaInicio = $request->getGet('fecha_inicio');
        $fechaFin    = $request->getGet('fecha_fin');
        $periodo     = $request->getGet('periodo');
        $nivel       = $request->getGet('nivel');
        $origen      = $request->getGet('origen') ?? '';
        $metodoPago  = $request->getGet('metodo_pago') ?? '';

        // Cajero: siempre restringido a sus propios registros
        $idCajero = ($rol === 'cajero') ? $idSesion : $request->getGet('id_cajero');

        if ($periodo === 'hoy') {
            $fechaInicio = date('Y-m-d');
            $fechaFin    = date('Y-m-d');
        } elseif ($periodo === 'semana') {
            $fechaInicio = date('Y-m-d', strtotime('monday this week'));
            $fechaFin    = date('Y-m-d');
        } elseif ($periodo === 'mes') {
            $fechaInicio = date('Y-m-01');
            $fechaFin    = date('Y-m-d');
        }

        $pagos = [];

        if ($origen !== 'externos') {
            $b = $db->table('pagos p')
                ->select('p.id, p.folio_digital, p.num_control, p.nombre_alumno AS nombre, p.concepto, p.detalle_tramite, p.nivel, p.modalidad, p.monto, p.metodo_pago, p.observaciones, p.created_at, u.nombre AS nombre_cajero')
                ->join('usuarios u', 'u.id = p.id_cajero', 'left');
            if ($fechaInicio) $b->where('DATE(p.created_at) >=', $fechaInicio);
            if ($fechaFin)    $b->where('DATE(p.created_at) <=', $fechaFin);
            if ($idCajero)    $b->where('p.id_cajero', (int) $idCajero);
            if ($nivel)       $b->where('p.nivel', $nivel);
            if ($metodoPago)  $b->where('p.metodo_pago', $metodoPago);
            foreach ($b->get()->getResultArray() as $r) {
                $r['tipo_pago'] = 'alumno';
                $pagos[] = $r;
            }
        }

        if ($origen !== 'alumnos') {
            $b = $db->table('pagos_externos pe')
                ->select('pe.id, pe.folio_digital, pe.nombre_cliente AS nombre, pe.concepto, pe.nivel, pe.monto, pe.metodo_pago, pe.observaciones, pe.created_at, u.nombre AS nombre_cajero')
                ->join('usuarios u', 'u.id = pe.id_cajero', 'left');
            if ($fechaInicio) $b->where('DATE(pe.created_at) >=', $fechaInicio);
            if ($fechaFin)    $b->where('DATE(pe.created_at) <=', $fechaFin);
            if ($idCajero)    $b->where('pe.id_cajero', (int) $idCajero);
            if ($nivel)       $b->where('pe.nivel', $nivel);
            if ($metodoPago)  $b->where('pe.metodo_pago', $metodoPago);
            foreach ($b->get()->getResultArray() as $r) {
                $r['tipo_pago']       = 'externo';
                $r['num_control']     = null;
                $r['detalle_tramite'] = null;
                $pagos[] = $r;
            }
        }

        usort($pagos, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        $totalEfectivo       = array_sum(array_column(array_filter($pagos, fn($p) => in_array($p['metodo_pago'] ?? 'Efectivo', ['Efectivo', ''])), 'monto'));
        $totalTransferencia  = array_sum(array_column(array_filter($pagos, fn($p) => ($p['metodo_pago'] ?? '') === 'Transferencia'), 'monto'));
        $totalDeposito       = array_sum(array_column(array_filter($pagos, fn($p) => ($p['metodo_pago'] ?? '') === 'Depósito bancario'), 'monto'));
        $totalTarjetaDebito  = array_sum(array_column(array_filter($pagos, fn($p) => ($p['metodo_pago'] ?? '') === 'Tarjeta de débito'), 'monto'));
        $totalTarjetaCredito = array_sum(array_column(array_filter($pagos, fn($p) => ($p['metodo_pago'] ?? '') === 'Tarjeta de crédito'), 'monto'));

        return [
            'pagos'               => $pagos,
            'totalGeneral'        => array_sum(array_column($pagos, 'monto')),
            'totalEfectivo'       => $totalEfectivo,
            'totalTransferencia'  => $totalTransferencia,
            'totalDeposito'       => $totalDeposito,
            'totalTarjetaDebito'  => $totalTarjetaDebito,
            'totalTarjetaCredito' => $totalTarjetaCredito,
            'filtros'             => compact('fechaInicio', 'fechaFin', 'periodo', 'idCajero', 'nivel', 'origen', 'metodoPago'),
            'rol'                 => $rol,
            'idSesion'            => $idSesion,
        ];
    }

    public function reportes()
    {
        if ($guard = $this->checkAuth()) {
            return $guard;
        }

        $data    = $this->filtrarPagos();
        $cajeros = \Config\Database::connect()
            ->table('usuarios')->select('id, nombre')->orderBy('nombre')->get()->getResultArray();

        return view('admin/reportes', array_merge($data, ['cajeros' => $cajeros]));
    }

    public function exportarCSV()
    {
        if ($guard = $this->checkAuth()) {
            return $guard;
        }

        ['pagos' => $pagos, 'totalGeneral' => $total, 'totalEfectivo' => $totalEfectivo, 'totalTransferencia' => $totalTransferencia, 'totalDeposito' => $totalDeposito, 'totalTarjetaDebito' => $totalTarjetaDebito, 'totalTarjetaCredito' => $totalTarjetaCredito] = $this->filtrarPagos();

        $conceptoLabels = [
            'inscripcion'   => 'Inscripción',
            'reinscripcion' => 'Reinscripción',
            'mensualidad'   => 'Mensualidad',
            'tramite'       => 'Trámite',
        ];
        $nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];

        $esc = fn(string $v): string => '"' . str_replace('"', '""', $v) . '"';

        $lines   = [];
        $lines[] = implode(',', array_map($esc, ['Folio', 'Tipo', 'Fecha', 'Nombre', 'Concepto', 'Nivel', 'Cajero', 'Efectivo', 'Transferencia', 'Depósito bancario', 'Tarjeta de débito', 'Tarjeta de crédito']));

        foreach ($pagos as $p) {
            $tipoLabel = $p['tipo_pago'] === 'externo' ? 'Externo/Aspirante' : 'Alumno';

            if ($p['tipo_pago'] === 'alumno') {
                $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
                if (($p['nivel'] ?? '') === 'posgrado' && $p['concepto'] === 'mensualidad') {
                    $concepto = mb_stripos($p['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
                } elseif ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
                    $concepto .= ' - ' . $p['detalle_tramite'];
                }
            } else {
                $concepto = $p['concepto'];
            }

            $nivelLabel = ! empty($p['nivel']) ? ($nivelLabels[$p['nivel']] ?? $p['nivel']) : '—';

            $metodo = $p['metodo_pago'] ?? 'Efectivo';
            $lines[] = implode(',', array_map($esc, [
                $p['folio_digital'] ?? '',
                $tipoLabel,
                date('d/m/Y H:i', strtotime($p['created_at'])),
                $p['nombre'] ?? '',
                $concepto,
                $nivelLabel,
                $p['nombre_cajero'] ?? 'N/D',
                in_array($metodo, ['Efectivo', ''])     ? number_format((float) $p['monto'], 2, '.', '') : '',
                $metodo === 'Transferencia'              ? number_format((float) $p['monto'], 2, '.', '') : '',
                $metodo === 'Depósito bancario'          ? number_format((float) $p['monto'], 2, '.', '') : '',
                $metodo === 'Tarjeta de débito'          ? number_format((float) $p['monto'], 2, '.', '') : '',
                $metodo === 'Tarjeta de crédito'         ? number_format((float) $p['monto'], 2, '.', '') : '',
            ]));
        }

        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'Total Efectivo',          number_format((float) $totalEfectivo,      2, '.', ''), '', '', '', '']));
        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'Total Transferencia',    '', number_format((float) $totalTransferencia, 2, '.', ''), '', '', '']));
        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'Total Depósito bancario','', '', number_format((float) $totalDeposito,       2, '.', ''), '', '']));
        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'Total T. de débito',     '', '', '', number_format((float) $totalTarjetaDebito,  2, '.', ''), '']));
        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'Total T. de crédito',    '', '', '', '', number_format((float) $totalTarjetaCredito, 2, '.', '')]));
        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', '', 'TOTAL',                  number_format((float) $total,               2, '.', ''), '', '', '', '']));

        $csv      = "\xEF\xBB\xBF" . implode("\r\n", $lines);
        $filename = 'reporte-pagos-' . date('Ymd-His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($csv);
    }

    public function exportarPDF()
    {
        if ($guard = $this->checkAuth()) {
            return $guard;
        }

        $data = $this->filtrarPagos();

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('admin/reporte_pdf', $data));
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'reporte-pagos-' . date('Ymd-His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function dashboard()
    {
        $session = service('session');
        if (! $session->get('logged_in') || ! in_array($session->get('rol'), ['admin', 'cajero'], true)) {
            return redirect()->to(base_url('auth/login'));
        }

        $rol       = $session->get('rol');
        $idUsuario = (int) $session->get('id_usuario');
        $db        = \Config\Database::connect();
        $hoy       = date('Y-m-d');

        $pagosRecientes    = [];
        $externosRecientes = [];
        $actividadReciente = [];

        if ($rol === 'admin') {

            $totalAlumnos = (float) ($db->table('pagos')
                ->selectSum('monto', 'total')
                ->where('DATE(created_at)', $hoy)
                ->get()->getRowArray()['total'] ?? 0);

            $totalExternos = (float) ($db->table('pagos_externos')
                ->selectSum('monto', 'total')
                ->where('DATE(created_at)', $hoy)
                ->get()->getRowArray()['total'] ?? 0);

            $pagosAlumnos = (int) $db->table('pagos')
                ->where('DATE(created_at)', $hoy)
                ->countAllResults();

            $pagosExternos = (int) $db->table('pagos_externos')
                ->where('DATE(created_at)', $hoy)
                ->countAllResults();

            $alumnosHoy = (int) ($db->table('pagos')
                ->select('COUNT(DISTINCT num_control) AS cnt')
                ->where('DATE(created_at)', $hoy)
                ->get()->getRowArray()['cnt'] ?? 0);

            $externasHoy = (int) $db->table('pagos_externos')
                ->where('DATE(created_at)', $hoy)
                ->countAllResults();

            $pagosRecientes = $db->table('pagos p')
                ->select('p.id, p.folio_digital, p.nombre_alumno, p.concepto, p.detalle_tramite, p.nivel, p.modalidad, p.monto, p.metodo_pago, p.created_at, u.nombre AS nombre_cajero')
                ->join('usuarios u', 'u.id = p.id_cajero', 'left')
                ->orderBy('p.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();

            $externosRecientes = $db->table('pagos_externos pe')
                ->select('pe.id, pe.folio_digital, pe.nombre_cliente, pe.concepto, pe.monto, pe.metodo_pago, pe.created_at, u.nombre AS nombre_cajero')
                ->join('usuarios u', 'u.id = pe.id_cajero', 'left')
                ->orderBy('pe.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();

        } else {
            // Cajero — solo registros propios del día

            $totalAlumnos = (float) ($db->table('pagos')
                ->selectSum('monto', 'total')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->get()->getRowArray()['total'] ?? 0);

            $totalExternos = (float) ($db->table('pagos_externos')
                ->selectSum('monto', 'total')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->get()->getRowArray()['total'] ?? 0);

            $pagosAlumnos = (int) $db->table('pagos')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->countAllResults();

            $pagosExternos = (int) $db->table('pagos_externos')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->countAllResults();

            $alumnosHoy = (int) ($db->table('pagos')
                ->select('COUNT(DISTINCT num_control) AS cnt')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->get()->getRowArray()['cnt'] ?? 0);

            $externasHoy = (int) $db->table('pagos_externos')
                ->where('DATE(created_at)', $hoy)
                ->where('id_cajero', $idUsuario)
                ->countAllResults();

            // Actividad reciente unificada
            $rawAlumnos = $db->table('pagos p')
                ->select('p.id, p.folio_digital, p.nombre_alumno AS nombre, p.concepto, p.detalle_tramite, p.nivel, p.modalidad, p.monto, p.metodo_pago, p.created_at')
                ->where('p.id_cajero', $idUsuario)
                ->orderBy('p.created_at', 'DESC')
                ->limit(15)
                ->get()->getResultArray();

            $rawExternos = $db->table('pagos_externos pe')
                ->select('pe.id, pe.folio_digital, pe.nombre_cliente AS nombre, pe.concepto, pe.monto, pe.metodo_pago, pe.created_at')
                ->where('pe.id_cajero', $idUsuario)
                ->orderBy('pe.created_at', 'DESC')
                ->limit(15)
                ->get()->getResultArray();

            foreach ($rawAlumnos as &$r) {
                $r['tipo_pago'] = 'alumno';
            }
            unset($r);
            foreach ($rawExternos as &$r) {
                $r['tipo_pago']       = 'externo';
                $r['detalle_tramite'] = null;
            }
            unset($r);

            $actividadReciente = array_merge($rawAlumnos, $rawExternos);
            usort($actividadReciente, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
            $actividadReciente = array_slice($actividadReciente, 0, 15);
        }

        return view('admin/dashboard', [
            'rol'               => $rol,
            'totalHoy'          => $totalAlumnos + $totalExternos,
            'pagosHoy'          => $pagosAlumnos + $pagosExternos,
            'personasHoy'       => $alumnosHoy + $externasHoy,
            'pagosRecientes'    => $pagosRecientes,
            'externosRecientes' => $externosRecientes,
            'actividadReciente' => $actividadReciente,
        ]);
    }

    public function editarPago(int $id)
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $db   = \Config\Database::connect();
        $pago = $db->table('pagos p')
            ->select('p.*, u.nombre AS nombre_cajero')
            ->join('usuarios u', 'u.id = p.id_cajero', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago #{$id} no encontrado.");
        }

        $conceptosTramites = \Config\Database::connect()
            ->table('conceptos_tramites')
            ->where('estatus', 'activo')
            ->orderBy('nombre_tramite')
            ->get()->getResultArray();

        return view('admin/editar_pago', [
            'pago'              => $pago,
            'conceptosTramites' => $conceptosTramites,
        ]);
    }

    public function actualizarPago(int $id)
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $db      = \Config\Database::connect();
        $session = service('session');
        $request = service('request');

        $pagoAntes = $db->table('pagos')->where('id', $id)->get()->getRowArray();

        if (! $pagoAntes) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago #{$id} no encontrado.");
        }

        $cambios = [
            'concepto'        => $request->getPost('concepto'),
            'detalle_tramite' => $request->getPost('detalle_tramite') ?: null,
            'periodo_pago'    => $request->getPost('periodo_pago') ?: null,
            'monto'           => $request->getPost('monto'),
        ];

        $db->table('pagos')->where('id', $id)->update($cambios);

        $db->table('bitacora_pagos')->insert([
            'id_pago'       => $id,
            'folio_digital' => $pagoAntes['folio_digital'],
            'id_admin'      => $session->get('id_usuario'),
            'accion'        => 'edicion',
            'detalle'       => json_encode([
                'antes'   => array_intersect_key($pagoAntes, $cambios),
                'despues' => $cambios,
            ], JSON_UNESCAPED_UNICODE),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/reportes'))
            ->with('success', "Pago {$pagoAntes['folio_digital']} actualizado correctamente.");
    }

    public function estadoCuenta()
    {
        $session = service('session');
        if (! $session->get('logged_in') || ! in_array($session->get('rol'), ['admin', 'cajero'], true)) {
            return redirect()->to(base_url('auth/login'));
        }

        $numControl = trim($this->request->getGet('num_control') ?? '');
        $nivel      = $this->request->getGet('nivel') ?? '';
        $anio       = (int) ($this->request->getGet('anio') ?? date('Y'));

        $data = [
            'num_control'     => $numControl,
            'nivel'           => $nivel,
            'anio'            => $anio,
            'estado'          => [],
            'info_alumno'     => null,
            'anios'           => [],
            'inscripciones'   => [],
            'pagos_otros'     => [],
            'totales'              => null,
            'directa_anio'         => false,
            'periodo_actual'       => null,
            'es_posgrado'          => ($nivel === 'posgrado'),
            'materias_estado'      => [],
            'pagos_detalle_meses'  => [],
        ];

        if ($numControl && $nivel) {
            $model = new \App\Models\AdeudoModel();

            if ($nivel === 'posgrado') {
                $dbUni   = \Config\Database::connect('uni');
                $alumRow = $dbUni->table('alumnos_datos_personales adp')
                                 ->select('lic.id AS clavelicen')
                                 ->join('grupos_modalidad gm', 'gm.id_grupos = adp.id_grupo', 'left')
                                 ->join('licenciaturas lic', 'lic.id = gm.licenciatura', 'left')
                                 ->where('adp.numero_control', $numControl)
                                 ->get()->getRowArray();

                if ($alumRow && ! empty($alumRow['clavelicen'])) {
                    $matRows = $dbUni->table('materias')
                                     ->select('materia, clavemateria')
                                     ->where('clavelicen', $alumRow['clavelicen'])
                                     ->orderBy('id', 'ASC')
                                     ->get()->getResultArray();

                    if (! empty($matRows)) {
                        $matNombres = array_column($matRows, 'materia');
                        $dbApp      = \Config\Database::connect();
                        $pagosRows  = $dbApp->table('pagos')
                                            ->select('detalle_tramite, folio_digital, monto, created_at')
                                            ->where('num_control', $numControl)
                                            ->where('nivel', 'posgrado')
                                            ->where('concepto', 'mensualidad')
                                            ->whereIn('detalle_tramite', $matNombres)
                                            ->get()->getResultArray();
                        $pagosMap = [];
                        foreach ($pagosRows as $p) {
                            $pagosMap[$p['detalle_tramite']] = $p;
                        }
                        foreach ($matRows as $m) {
                            $pago = $pagosMap[$m['materia']] ?? null;
                            $data['materias_estado'][] = [
                                'nombre' => $m['materia'],
                                'clave'  => $m['clavemateria'] ?? '',
                                'pagada' => $pago !== null,
                                'folio'  => $pago['folio_digital'] ?? null,
                                'fecha'  => $pago ? date('d/m/Y', strtotime($pago['created_at'])) : null,
                                'monto'  => $pago ? (float) $pago['monto'] : null,
                            ];
                        }
                    }
                }
            }

            $data['estado']                = $model->getEstadoCuentaMensual($numControl, $nivel, $anio);
            $data['pagos_detalle_meses']   = $model->getPagosDetallePorMes($numControl, $nivel, $anio);
            $data['info_alumno']           = $model->getInfoAlumno($numControl, $nivel);
            $data['anios']                 = $model->getAniosConPagos($numControl, $nivel);
            $data['inscripciones']  = $model->getInscripciones($numControl, $nivel);
            $data['pagos_otros']    = $model->getPagosOtros($numControl, $nivel);
            $data['totales']        = $model->getTotalesPagado($numControl, $nivel);

            // Ancla del año seleccionado → modo directa
            $anclaAnio = $model->getAnclaParaAnio($numControl, $nivel, $anio);
            $data['directa_anio'] = $anclaAnio['directa'];

            // Periodo actual: ancla del año en curso para el encabezado
            $anclaHoy = ($anio === (int) date('Y'))
                ? $anclaAnio
                : $model->getAnclaParaAnio($numControl, $nivel, (int) date('Y'));

            if (! $anclaHoy['directa'] && $anclaHoy['pago'] !== null) {
                $pHoy = $anclaHoy['pago'];
                $num  = ! empty($pHoy['periodo_pago']) ? (int) $pHoy['periodo_pago'] : null;
                $plan = $pHoy['detalle_tramite'] ?? '';

                if ($num) {
                    if ($plan === 'Semestral') {
                        $rango = ($num % 2 !== 0) ? 'Ago – Dic' : 'Feb – Jul';
                        $data['periodo_actual'] = 'Semestre ' . $num . ' (' . $rango . ')';
                    } elseif ($nivel === 'uni' || $plan === 'Cuatrimestral') {
                        $mod   = $num % 3;
                        $rango = $mod === 1 ? 'Ene – Abr' : ($mod === 2 ? 'May – Ago' : 'Sep – Dic');
                        $data['periodo_actual'] = 'Cuatrimestre ' . $num . ' (' . $rango . ')';
                    } else {
                        $data['periodo_actual'] = 'Periodo ' . $num;
                        if (! empty($pHoy['tipo_periodo'])) {
                            $data['periodo_actual'] .= ' — ' . $pHoy['tipo_periodo'];
                        }
                    }
                } else {
                    // Fallback por mes si el pago no tiene periodo_pago
                    $m = $anclaHoy['mes'];
                    $data['periodo_actual'] = $m <= 4
                        ? 'Cuatrimestre 1 (Ene – Abr)'
                        : ($m <= 8 ? 'Cuatrimestre 2 (May – Ago)' : 'Cuatrimestre 3 (Sep – Dic)');
                }
            }
        }

        return view('admin/estado_cuenta', $data);
    }

    public function morosos()
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $nivel = $this->request->getGet('nivel') ?? '';
        $model = new \App\Models\AdeudoModel();

        return view('admin/morosos', [
            'morosos' => $model->getMorosos($nivel ?: null),
            'nivel'   => $nivel,
        ]);
    }

    public function eliminarPago(int $id)
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $db      = \Config\Database::connect();
        $session = service('session');

        $pago = $db->table('pagos')->where('id', $id)->get()->getRowArray();

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago #{$id} no encontrado.");
        }

        $db->table('bitacora_pagos')->insert([
            'id_pago'       => $id,
            'folio_digital' => $pago['folio_digital'],
            'id_admin'      => $session->get('id_usuario'),
            'accion'        => 'eliminacion',
            'detalle'       => json_encode($pago, JSON_UNESCAPED_UNICODE),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $db->table('pagos')->where('id', $id)->delete();

        return redirect()->to(base_url('admin/reportes'))
            ->with('success', "Pago {$pago['folio_digital']} eliminado. Acción registrada en bitácora.");
    }
}
