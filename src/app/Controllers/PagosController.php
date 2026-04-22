<?php

namespace App\Controllers;

use App\Models\ConceptoTramiteModel;
use App\Models\PagoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PagosController extends BaseController
{
    public function index()
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        return view('pagos/registro');
    }

    public function tramitesDisponibles()
    {
        if (! service('session')->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $nivel  = $this->request->getGet('nivel') ?? '';
        $model  = new ConceptoTramiteModel();

        return $this->response->setJSON($model->activosPorNivel($nivel));
    }

    public function buscarAlumno()
    {
        $numControl = trim($this->request->getGet('num_control') ?? '');
        $nivel      = $this->request->getGet('nivel');

        if (empty($numControl) || empty($nivel)) {
            return $this->response->setJSON(['found' => false]);
        }

        if ($nivel === 'uni') {
            $db  = \Config\Database::connect('uni');
            $row = $db->table('alumnos_datos_personales')
                      ->select('Nombres, apellido_paterno, apellido_materno, Licenciatura, modalidad')
                      ->where('numero_control', $numControl)
                      ->get()->getRowArray();

            if (! $row) {
                return $this->response->setJSON(['found' => false]);
            }

            return $this->response->setJSON([
                'found'    => true,
                'nombre'   => trim("{$row['Nombres']} {$row['apellido_paterno']} {$row['apellido_materno']}"),
                'carrera'  => $row['Licenciatura'],
                'modalidad' => $row['modalidad'],
            ]);
        }

        if ($nivel === 'prepa') {
            $db  = \Config\Database::connect('prepa');
            $row = $db->table('alumno_datos')
                      ->select('nombres, apellido_paterno, apellido_materno')
                      ->where('numero_control', $numControl)
                      ->get()->getRowArray();

            if (! $row) {
                return $this->response->setJSON(['found' => false]);
            }

            return $this->response->setJSON([
                'found'    => true,
                'nombre'   => trim("{$row['nombres']} {$row['apellido_paterno']} {$row['apellido_materno']}"),
                'carrera'  => null,
                'modalidad' => null,
            ]);
        }

        return $this->response->setJSON(['found' => false]);
    }

    public function registrar()
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $data = [
            'num_control'     => $this->request->getPost('num_control'),
            'nivel'           => $this->request->getPost('nivel'),
            'nombre_alumno'   => $this->request->getPost('nombre_alumno'),
            'modalidad'       => $this->request->getPost('modalidad') ?: null,
            'carrera'         => $this->request->getPost('carrera') ?: null,
            'concepto'        => $this->request->getPost('concepto'),
            'detalle_tramite' => $this->request->getPost('detalle_tramite') ?: null,
            'periodo_pago'    => $this->request->getPost('periodo_pago') ?: null,
            'monto'           => $this->request->getPost('monto'),
            'id_cajero'       => service('session')->get('id_usuario'),
        ];

        $model = new PagoModel();

        if (! $model->insert($data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Error al guardar el pago. Intente de nuevo.',
                ]);
            }
            return view('pagos/registro', ['error' => 'Error al guardar el pago. Intente de nuevo.']);
        }

        $insertId     = $model->getInsertID();
        $inserted     = $model->find($insertId);
        $folioDigital = date('Ymd') . '-' . $insertId . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $selloDigital = hash('sha256', $folioDigital . $inserted['created_at'] . 'LLAVE_SECRETA');

        $model->update($insertId, [
            'folio_digital' => $folioDigital,
            'sello_digital' => $selloDigital,
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'       => true,
                'folio_digital' => $folioDigital,
                'pdf_url'       => base_url('pagos/comprobante/' . $folioDigital),
                'csrf_name'     => csrf_token(),
                'csrf_hash'     => csrf_hash(),
            ]);
        }

        return redirect()->to(base_url('pagos/comprobante/' . $folioDigital));
    }

    public function comprobante(string $folio)
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $model = new PagoModel();
        $pago  = $model->where('folio_digital', $folio)->first();

        if (! $pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Folio no encontrado: {$folio}");
        }

        $db     = \Config\Database::connect();
        $cajero = $db->table('usuarios')
                     ->select('nombre')
                     ->where('id', $pago['id_cajero'])
                     ->get()->getRowArray();

        $niveles   = ['uni' => 'Universidad', 'prepa' => 'Preparatoria', 'posgrado' => 'Posgrado'];
        $conceptos = [
            'inscripcion'   => 'Inscripción',
            'reinscripcion' => 'Reinscripción',
            'mensualidad'   => 'Mensualidad',
            'tramite'       => 'Trámite',
        ];
        $detalles = [
            'constancia'     => 'Constancia Escolar',
            'constancia_ext' => 'Constancia Extranjero',
            'historial'      => 'Historial de Calificaciones',
            'gafete'         => 'Gafete',
        ];

        $conceptoLabel = $conceptos[$pago['concepto']] ?? $pago['concepto'];
        if ($pago['concepto'] === 'tramite' && ! empty($pago['detalle_tramite'])) {
            $conceptoLabel .= ' — ' . ($detalles[$pago['detalle_tramite']] ?? $pago['detalle_tramite']);
        }

        $logoMap = [
            'uni'      => 'logosuni.jpeg',
            'prepa'    => 'logosba.jpeg',
            'posgrado' => 'logosuni.jpeg',
        ];
        $logoFile   = FCPATH . 'assets/img/' . ($logoMap[$pago['nivel']] ?? '');
        $logoBase64 = null;
        if ($logoFile !== FCPATH . 'assets/img/' && file_exists($logoFile)) {
            $ext        = pathinfo($logoFile, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        $viewData = [
            'pago'          => $pago,
            'nivelLabel'    => $niveles[$pago['nivel']] ?? $pago['nivel'],
            'conceptoLabel' => $conceptoLabel,
            'fechaHora'     => date('d/m/Y H:i:s', strtotime($pago['created_at'])),
            'montoFormato'  => '$' . number_format((float) $pago['monto'], 2),
            'montoLetras'   => $this->numeroALetras((float) $pago['monto']),
            'nombreCajero'  => $cajero['nombre'] ?? 'N/D',
            'logoBase64'    => $logoBase64,
            'selloDigital'  => $pago['sello_digital'] ?? '',
        ];

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pagos/comprobante_pdf', $viewData));
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="comprobante-' . $folio . '.pdf"')
            ->setBody($dompdf->output());
    }

    // ────────────────────────────────────────────────────────────────────────────

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
            if ($n > 0) {
                $texto .= ' ';
            }
        }

        if ($n >= 1000) {
            $miles  = (int) ($n / 1000);
            $texto .= $miles === 1 ? 'MIL' : $this->enteroALetras($miles) . ' MIL';
            $n     %= 1000;
            if ($n > 0) {
                $texto .= ' ';
            }
        }

        if ($n >= 100) {
            $texto .= $n === 100 ? 'CIEN' : $centenas[(int) ($n / 100)];
            $n     %= 100;
            if ($n > 0) {
                $texto .= ' ';
            }
        }

        if ($n > 0) {
            if ($n < 30) {
                $texto .= $unidades[$n];
            } else {
                $texto .= $decenas[(int) ($n / 10)];
                $r      = $n % 10;
                if ($r > 0) {
                    $texto .= ' Y ' . $unidades[$r];
                }
            }
        }

        return trim($texto);
    }
}
