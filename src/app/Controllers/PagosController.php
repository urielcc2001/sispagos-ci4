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

    public function verificarAdeudos()
    {
        if (! service('session')->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $numControl = trim($this->request->getGet('num_control') ?? '');
        $nivel      = $this->request->getGet('nivel') ?? '';

        if (! $numControl || ! $nivel || $nivel === 'posgrado') {
            return $this->response->setJSON(['adeudos' => []]);
        }

        $model   = new \App\Models\AdeudoModel();
        $adeudos = $model->getAdeudosParaAlerta($numControl, $nivel);

        return $this->response->setJSON(['adeudos' => $adeudos]);
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

        if ($nivel === 'posgrado') {
            $db  = \Config\Database::connect('uni');
            $row = $db->table('alumnos_datos_personales')
                      ->select('Nombres, apellido_paterno, apellido_materno')
                      ->where('numero_control', $numControl)
                      ->get()->getRowArray();

            if (! $row) {
                return $this->response->setJSON(['found' => false, 'external' => true]);
            }

            return $this->response->setJSON([
                'found'    => true,
                'nombre'   => trim("{$row['Nombres']} {$row['apellido_paterno']} {$row['apellido_materno']}"),
                'carrera'  => null,
                'modalidad' => null,
            ]);
        }

        return $this->response->setJSON(['found' => false]);
    }

    public function ultimoPago()
    {
        if (! service('session')->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $numControl = trim($this->request->getGet('num_control') ?? '');
        $concepto   = $this->request->getGet('concepto') ?? '';

        if (empty($numControl) || empty($concepto)) {
            return $this->response->setJSON(['found' => false]);
        }

        $model  = new PagoModel();
        $ultimo = $model
            ->where('num_control', $numControl)
            ->where('concepto', $concepto)
            ->orderBy('id', 'DESC')
            ->first();

        // Sin reinscripción previa → buscar inscripción para sugerir periodo 2
        if ((! $ultimo || empty($ultimo['periodo_pago'])) && $concepto === 'reinscripcion') {
            $inscripcion = $model
                ->where('num_control', $numControl)
                ->where('concepto', 'inscripcion')
                ->orderBy('id', 'DESC')
                ->first();

            if (! $inscripcion) {
                return $this->response->setJSON(['found' => false]);
            }

            return $this->response->setJSON([
                'found'        => true,
                'actual'       => 1,
                'sugerido'     => 2,
                'anio'         => (int) date('Y'),
                'tipo_periodo' => $inscripcion['tipo_periodo']    ?? null,
                'modalidad'    => $inscripcion['modalidad']       ?? null,
                'plan_bach'    => $inscripcion['detalle_tramite'] ?? null,
            ]);
        }

        if (! $ultimo || empty($ultimo['periodo_pago'])) {
            return $this->response->setJSON(['found' => false]);
        }

        $actual   = (int) $ultimo['periodo_pago'];
        $sugerido = $actual + 1;
        $anio     = (int) date('Y');

        if ($concepto === 'mensualidad' && $sugerido > 12) {
            $sugerido = 1;
            $anio++;
        }

        return $this->response->setJSON([
            'found'        => true,
            'actual'       => $actual,
            'sugerido'     => $sugerido,
            'anio'         => $anio,
            'tipo_periodo' => $ultimo['tipo_periodo']    ?? null,
            'modalidad'    => $ultimo['modalidad']       ?? null,
            'plan_bach'    => $ultimo['detalle_tramite'] ?? null,
        ]);
    }

    public function registrar()
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $concepto      = $this->request->getPost('concepto');
        $periodoNum    = $this->request->getPost('periodo_pago')    ?: null;
        $tipoPeriodo   = $this->request->getPost('tipo_periodo')    ?: null;
        $fechaPagoReal = $this->request->getPost('fecha_pago_real') ?: null;

        if ($concepto !== 'mensualidad') {
            $fechaPagoReal = null;
        }

        $errorValidacion = $this->validarSecuenciaPago(
            $concepto,
            $this->request->getPost('num_control'),
            $this->request->getPost('nivel'),
            $periodoNum,
            $fechaPagoReal
        );
        if ($errorValidacion) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $errorValidacion,
                ]);
            }
            return view('pagos/registro', ['error' => $errorValidacion]);
        }

        $data = [
            'num_control'     => $this->request->getPost('num_control'),
            'nivel'           => $this->request->getPost('nivel'),
            'nombre_alumno'   => $this->request->getPost('nombre_alumno'),
            'modalidad'       => $this->request->getPost('modalidad') ?: null,
            'carrera'         => $this->request->getPost('carrera') ?: null,
            'concepto'        => $concepto,
            'detalle_tramite' => $this->request->getPost('detalle_tramite') ?: null,
            'periodo_pago'    => $periodoNum !== null ? (int) $periodoNum : null,
            'tipo_periodo'    => $tipoPeriodo,
            'fecha_pago_real' => $fechaPagoReal,
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

        $mesesNombres  = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $periodoDisplay = '—';
        $periodoLabel   = 'Periodo';

        if ($pago['nivel'] === 'posgrado' && $pago['concepto'] === 'mensualidad') {
            $periodoLabel   = 'Materia';
            $periodoDisplay = $pago['detalle_tramite'] ?? '—';
        } elseif (! empty($pago['periodo_pago'])) {
            $num  = (int) $pago['periodo_pago'];
            $plan = $pago['detalle_tramite'] ?? '';

            if ($pago['concepto'] === 'mensualidad') {
                $mesNombre      = $mesesNombres[$num - 1] ?? '?';
                $anio           = date('Y', strtotime($pago['created_at']));
                $periodoDisplay = $mesNombre . ' ' . $anio;
            } elseif ($plan === 'Semestral') {
                $rango          = ($num % 2 !== 0) ? 'Agosto - Diciembre' : 'Febrero - Julio';
                $periodoDisplay = 'Semestre ' . $num . ' (' . $rango . ')';
            } elseif ($pago['nivel'] === 'uni' || $plan === 'Cuatrimestral') {
                $mod            = $num % 3;
                if ($mod === 1)     $rango = 'Enero - Abril';
                elseif ($mod === 2) $rango = 'Mayo - Agosto';
                else                $rango = 'Septiembre - Diciembre';
                $periodoDisplay = 'Cuatrimestre ' . $num . ' (' . $rango . ')';
            } else {
                $periodoDisplay = 'Periodo ' . $num;
                if (! empty($pago['tipo_periodo'])) {
                    $periodoDisplay .= ' — ' . $pago['tipo_periodo'];
                }
            }
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
            'pago'           => $pago,
            'nivelLabel'     => $niveles[$pago['nivel']] ?? $pago['nivel'],
            'conceptoLabel'  => $conceptoLabel,
            'periodoDisplay' => $periodoDisplay,
            'periodoLabel'   => $periodoLabel,
            'fechaHora'      => date('d/m/Y H:i:s', strtotime($pago['created_at'])),
            'montoFormato'   => '$' . number_format((float) $pago['monto'], 2),
            'montoLetras'    => $this->numeroALetras((float) $pago['monto']),
            'nombreCajero'   => $cajero['nombre'] ?? 'N/D',
            'logoBase64'     => $logoBase64,
            'selloDigital'   => $pago['sello_digital'] ?? '',
        ];

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

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

    private function validarSecuenciaPago(
        string  $concepto,
        ?string $numControl,
        ?string $nivel,
        ?string $periodoNum,
        ?string $fechaPagoReal
    ): ?string {
        if (! $numControl || ! $nivel || ! $concepto) {
            return null;
        }

        $db     = \Config\Database::connect();
        $meses  = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        if ($concepto === 'inscripcion') {
            $existe = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'inscripcion')
                ->countAllResults();
            if ($existe > 0) {
                return 'El alumno ya tiene una inscripción registrada para este nivel.';
            }
        }

        if ($concepto === 'reinscripcion') {
            $tieneInscripcion = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'inscripcion')
                ->countAllResults();
            if ($tieneInscripcion === 0) {
                return 'El alumno no tiene inscripción previa. Registra la inscripción primero.';
            }

            $periodo = (int) $periodoNum;
            if ($periodo > 2) {
                $previo = $db->table('pagos')
                    ->where('num_control', $numControl)
                    ->where('nivel', $nivel)
                    ->where('concepto', 'reinscripcion')
                    ->where('periodo_pago', $periodo - 1)
                    ->countAllResults();
                if ($previo === 0) {
                    return 'Error: Falta el pago de Reinscripción del Período ' . ($periodo - 1) . '.';
                }
            }
        }

        if ($concepto === 'mensualidad') {
            $inscripcion = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'inscripcion')
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()->getRowArray();

            if (! $inscripcion) {
                return 'El alumno no tiene inscripción registrada. No se puede cobrar mensualidad.';
            }

            if ($fechaPagoReal) {
                $mesInsc  = (int) date('n', strtotime($inscripcion['created_at']));
                $anioInsc = (int) date('Y', strtotime($inscripcion['created_at']));
                $mesMens  = (int) date('n', strtotime($fechaPagoReal));
                $anioMens = (int) date('Y', strtotime($fechaPagoReal));

                $tsInsc = mktime(0, 0, 0, $mesInsc, 1, $anioInsc);
                $tsMens = mktime(0, 0, 0, $mesMens, 1, $anioMens);

                if ($tsMens < $tsInsc) {
                    return 'No se puede registrar una mensualidad anterior a la inscripción '
                        . '(' . $meses[$mesInsc - 1] . ' ' . $anioInsc . ').';
                }
            }
        }

        return null;
    }

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
