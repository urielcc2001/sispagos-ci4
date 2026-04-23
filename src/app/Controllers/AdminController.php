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

    // ── Helper compartido: aplica filtros GET y devuelve pagos ──────
    private function filtrarPagos(): array
    {
        $request = service('request');
        $db      = \Config\Database::connect();

        $fechaInicio = $request->getGet('fecha_inicio');
        $fechaFin    = $request->getGet('fecha_fin');
        $periodo     = $request->getGet('periodo');
        $idCajero    = $request->getGet('id_cajero');
        $nivel       = $request->getGet('nivel');

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

        $builder = $db->table('pagos p')
            ->select('p.id, p.folio_digital, p.num_control, p.nombre_alumno, p.concepto, p.detalle_tramite, p.nivel, p.periodo_pago, p.monto, p.created_at, u.nombre AS nombre_cajero')
            ->join('usuarios u', 'u.id = p.id_cajero', 'left');

        if ($fechaInicio) $builder->where('DATE(p.created_at) >=', $fechaInicio);
        if ($fechaFin)    $builder->where('DATE(p.created_at) <=', $fechaFin);
        if ($idCajero)    $builder->where('p.id_cajero', (int) $idCajero);
        if ($nivel)       $builder->where('p.nivel', $nivel);

        $pagos = $builder->orderBy('p.created_at', 'DESC')->get()->getResultArray();

        return [
            'pagos'        => $pagos,
            'totalGeneral' => array_sum(array_column($pagos, 'monto')),
            'filtros'      => compact('fechaInicio', 'fechaFin', 'periodo', 'idCajero', 'nivel'),
        ];
    }

    public function reportes()
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $data    = $this->filtrarPagos();
        $cajeros = \Config\Database::connect()
            ->table('usuarios')->select('id, nombre')->orderBy('nombre')->get()->getResultArray();

        return view('admin/reportes', array_merge($data, ['cajeros' => $cajeros]));
    }

    public function exportarCSV()
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        ['pagos' => $pagos, 'totalGeneral' => $total, 'filtros' => $filtros] = $this->filtrarPagos();

        $conceptoLabels = [
            'inscripcion'   => 'Inscripción',
            'reinscripcion' => 'Reinscripción',
            'mensualidad'   => 'Mensualidad',
            'tramite'       => 'Trámite',
        ];
        $nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];

        $esc = fn(string $v): string => '"' . str_replace('"', '""', $v) . '"';

        $lines   = [];
        $lines[] = implode(',', array_map($esc, ['Folio', 'Fecha', 'Alumno', 'Concepto', 'Nivel', 'Cajero', 'Monto']));

        foreach ($pagos as $p) {
            $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
            if ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
                $concepto .= ' - ' . $p['detalle_tramite'];
            }
            $lines[] = implode(',', array_map($esc, [
                $p['folio_digital'] ?? '',
                date('d/m/Y H:i', strtotime($p['created_at'])),
                $p['nombre_alumno'],
                $concepto,
                $nivelLabels[$p['nivel']] ?? $p['nivel'],
                $p['nombre_cajero'] ?? 'N/D',
                number_format((float) $p['monto'], 2, '.', ''),
            ]));
        }

        $lines[] = implode(',', array_map($esc, ['', '', '', '', '', 'TOTAL GENERAL', number_format((float) $total, 2, '.', '')]));

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
        if ($guard = $this->checkAdmin()) {
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
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $db  = \Config\Database::connect();
        $hoy = date('Y-m-d');

        $totalHoy = (float) ($db->table('pagos')
            ->selectSum('monto', 'total')
            ->where('DATE(created_at)', $hoy)
            ->get()->getRowArray()['total'] ?? 0);

        $pagosHoy = (int) $db->table('pagos')
            ->where('DATE(created_at)', $hoy)
            ->countAllResults();

        $alumnosHoy = (int) ($db->query(
            'SELECT COUNT(DISTINCT num_control) AS cnt FROM pagos WHERE DATE(created_at) = CURDATE()'
        )->getRowArray()['cnt'] ?? 0);

        $pagosRecientes = $db->table('pagos p')
            ->select('p.id, p.folio_digital, p.nombre_alumno, p.concepto, p.detalle_tramite, p.monto, p.created_at, u.nombre AS nombre_cajero')
            ->join('usuarios u', 'u.id = p.id_cajero', 'left')
            ->orderBy('p.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return view('admin/dashboard', [
            'totalHoy'       => $totalHoy,
            'pagosHoy'       => $pagosHoy,
            'alumnosHoy'     => $alumnosHoy,
            'pagosRecientes' => $pagosRecientes,
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
