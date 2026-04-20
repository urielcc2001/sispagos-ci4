<?php

namespace App\Controllers;

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
            ->select('p.folio_digital, p.nombre_alumno, p.concepto, p.detalle_tramite, p.monto, p.created_at, u.nombre AS nombre_cajero')
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
}
