<?php

namespace App\Controllers;

use App\Models\PagoExternoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PagosExternosController extends BaseController
{
    public function index()
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        return view('pagos_externos/registro');
    }

    public function registrar()
    {
        if (! service('session')->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'No autorizado.']);
        }

        $nombre       = trim($this->request->getPost('nombre_cliente') ?? '');
        $nivel        = $this->request->getPost('nivel') ?? '';
        $modalidad    = $this->request->getPost('modalidad') ?? '';
        $conceptoSel  = $this->request->getPost('concepto') ?? '';
        $conceptoOtro = trim($this->request->getPost('concepto_otro') ?? '');
        $concepto     = ($conceptoSel === 'otro') ? $conceptoOtro : $conceptoSel;
        $monto        = $this->request->getPost('monto');
        $metodoPago   = $this->request->getPost('metodo_pago') ?: 'Efectivo';
        $observaciones = trim($this->request->getPost('observaciones') ?? '') ?: null;

        if (! $nombre || ! $nivel || ! $concepto || ! $monto) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Completa todos los campos obligatorios.',
            ]);
        }

        $data = [
            'nombre_cliente' => $nombre,
            'nivel'          => $nivel,
            'modalidad'      => $modalidad ?: null,
            'concepto'       => $concepto,
            'monto'          => (float) $monto,
            'metodo_pago'    => $metodoPago,
            'observaciones'  => $observaciones,
            'id_cajero'      => service('session')->get('id_usuario'),
        ];

        $model = new PagoExternoModel();

        if (! $model->insert($data)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Error al guardar el pago. Intente de nuevo.',
            ]);
        }

        $insertId     = $model->getInsertID();
        $folioDigital = 'EXT-' . date('Ymd') . '-' . $insertId . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $inserted     = $model->find($insertId);
        $selloDigital = hash('sha256', $folioDigital . $inserted['created_at'] . 'LLAVE_SECRETA');

        $model->update($insertId, [
            'folio_digital' => $folioDigital,
            'sello_digital' => $selloDigital,
        ]);

        return $this->response->setJSON([
            'success'       => true,
            'folio_digital' => $folioDigital,
            'pdf_url'       => base_url('pagos-externos/comprobante/' . $folioDigital),
            'csrf_name'     => csrf_token(),
            'csrf_hash'     => csrf_hash(),
        ]);
    }

    public function comprobante(string $folio)
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $model = new PagoExternoModel();
        $pago  = $model->where('folio_digital', $folio)->first();

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Folio no encontrado: {$folio}");
        }

        $db     = \Config\Database::connect();
        $cajero = $db->table('usuarios')
                     ->select('nombre')
                     ->where('id', $pago['id_cajero'])
                     ->get()->getRowArray();

        $niveles = ['uni' => 'Universidad', 'prepa' => 'Preparatoria', 'posgrado' => 'Posgrado'];

        $logoMap = [
            'uni'      => 'logosuni.jpeg',
            'prepa'    => 'logosba.jpeg',
            'posgrado' => 'logosuni.jpeg',
        ];
        $logoFile   = FCPATH . 'assets/img/' . ($logoMap[$pago['nivel']] ?? '');
        $logoBase64 = null;
        if (! empty($logoMap[$pago['nivel']]) && file_exists($logoFile)) {
            $ext        = pathinfo($logoFile, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        $viewData = [
            'pago'         => $pago,
            'nivelLabel'   => $niveles[$pago['nivel']] ?? $pago['nivel'],
            'fechaHora'    => date('d/m/Y H:i:s', strtotime($pago['created_at'])),
            'montoFormato' => '$' . number_format((float) $pago['monto'], 2),
            'montoLetras'  => $this->numeroALetras((float) $pago['monto']),
            'nombreCajero' => $cajero['nombre'] ?? 'N/D',
            'logoBase64'   => $logoBase64,
            'selloDigital' => $pago['sello_digital'] ?? '',
        ];

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pagos_externos/recibo_externo', $viewData));
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="recibo-ext-' . $folio . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function editar(int $id)
    {
        if (! service('session')->get('logged_in') || service('session')->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        $db   = \Config\Database::connect();
        $pago = $db->table('pagos_externos pe')
            ->select('pe.*, u.nombre AS nombre_cajero')
            ->join('usuarios u', 'u.id = pe.id_cajero', 'left')
            ->where('pe.id', $id)
            ->get()->getRowArray();

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago externo #{$id} no encontrado.");
        }

        return view('pagos_externos/editar', ['pago' => $pago]);
    }

    public function actualizar(int $id)
    {
        if (! service('session')->get('logged_in') || service('session')->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        $model = new PagoExternoModel();
        $pago  = $model->find($id);

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago externo #{$id} no encontrado.");
        }

        $model->update($id, [
            'nombre_cliente' => trim($this->request->getPost('nombre_cliente') ?? ''),
            'nivel'          => $this->request->getPost('nivel'),
            'modalidad'      => $this->request->getPost('modalidad') ?: null,
            'concepto'       => trim($this->request->getPost('concepto') ?? ''),
            'monto'          => (float) $this->request->getPost('monto'),
            'metodo_pago'    => $this->request->getPost('metodo_pago') ?: 'Efectivo',
            'observaciones'  => trim($this->request->getPost('observaciones') ?? '') ?: null,
        ]);

        return redirect()->to(base_url('dashboard'))
            ->with('success', "Pago externo {$pago['folio_digital']} actualizado correctamente.");
    }

    public function eliminar(int $id)
    {
        if (! service('session')->get('logged_in') || service('session')->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        $model = new PagoExternoModel();
        $pago  = $model->find($id);

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pago externo #{$id} no encontrado.");
        }

        $model->delete($id);

        return redirect()->to(base_url('dashboard'))
            ->with('success', "Pago externo {$pago['folio_digital']} eliminado.");
    }

    public function validar(string $sello)
    {
        $model = new PagoExternoModel();
        $pago  = $model->where('sello_digital', $sello)->first();

        if (! $pago) {
            return view('pagos_externos/validacion', ['valido' => false]);
        }

        $niveles = ['uni' => 'Universidad', 'prepa' => 'Preparatoria', 'posgrado' => 'Posgrado'];

        $logoMap = [
            'uni'      => 'logosuni.jpeg',
            'prepa'    => 'logosba.jpeg',
            'posgrado' => 'logosuni.jpeg',
        ];
        $logoFile   = FCPATH . 'assets/img/' . ($logoMap[$pago['nivel']] ?? '');
        $logoBase64 = null;
        if (! empty($logoMap[$pago['nivel']]) && file_exists($logoFile)) {
            $ext        = pathinfo($logoFile, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        return view('pagos_externos/validacion', [
            'valido'         => true,
            'nombre_cliente' => $pago['nombre_cliente'],
            'concepto'       => $pago['concepto'],
            'monto'          => '$' . number_format((float) $pago['monto'], 2),
            'folio'          => $pago['folio_digital'],
            'fecha'          => date('d/m/Y H:i', strtotime($pago['created_at'])),
            'logoBase64'     => $logoBase64,
            'nivelLabel'     => $niveles[$pago['nivel']] ?? $pago['nivel'],
        ]);
    }

    // ── Helpers de monto a letras ────────────────────────────────────────────

    private function numeroALetras(float $monto): string
    {
        $entero   = (int) $monto;
        $centavos = (int) round(($monto - $entero) * 100);
        $centStr  = str_pad($centavos, 2, '0', STR_PAD_LEFT);

        return $this->enteroALetras($entero) . " PESOS {$centStr}/100 M.N.";
    }

    private function enteroALetras(int $n): string
    {
        if ($n === 0) {
            return 'CERO';
        }

        $unidades = [
            '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE',
            'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE',
            'VEINTIÚN', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO',
            'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE',
        ];
        $decenas  = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = [
            '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
            'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
        ];

        $texto = '';

        if ($n >= 1000000) {
            $mill   = (int) ($n / 1000000);
            $texto .= $mill === 1 ? 'UN MILLÓN' : $this->enteroALetras($mill) . ' MILLONES';
            $n     %= 1000000;
            if ($n > 0) { $texto .= ' '; }
        }

        if ($n >= 1000) {
            $miles  = (int) ($n / 1000);
            $texto .= $miles === 1 ? 'MIL' : $this->enteroALetras($miles) . ' MIL';
            $n     %= 1000;
            if ($n > 0) { $texto .= ' '; }
        }

        if ($n >= 100) {
            $texto .= $n === 100 ? 'CIEN' : $centenas[(int) ($n / 100)];
            $n     %= 100;
            if ($n > 0) { $texto .= ' '; }
        }

        if ($n > 0) {
            if ($n < 30) {
                $texto .= $unidades[$n];
            } else {
                $texto .= $decenas[(int) ($n / 10)];
                $r      = $n % 10;
                if ($r > 0) { $texto .= ' Y ' . $unidades[$r]; }
            }
        }

        return trim($texto);
    }
}
