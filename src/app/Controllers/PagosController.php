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

    public function estadoMensualidades()
    {
        if (! service('session')->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $numControl = trim($this->request->getGet('num_control') ?? '');
        $nivel      = $this->request->getGet('nivel') ?? '';
        $anio       = (int) ($this->request->getGet('anio') ?: 0);

        if (! $numControl || ! $nivel || $nivel === 'posgrado') {
            return $this->response->setJSON(['meses' => [], 'directa' => false]);
        }

        $model  = new \App\Models\AdeudoModel();
        $result = $model->getEstadoMensualParaCobro($numControl, $nivel, $anio);

        return $this->response->setJSON([
            'meses'     => $result['meses'],
            'anio'      => $anio ?: (int) date('Y'),
            'directa'   => $result['directa'],
            'mes_ancla' => $result['mes_ancla'],
        ]);
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
            $db   = \Config\Database::connect('uni');
            $base = $db->table('alumnos_datos_personales')
                       ->select('Nombres, apellido_paterno, apellido_materno, id_grupo, Licenciatura, modalidad')
                       ->where('numero_control', $numControl)
                       ->get()->getRowArray();

            if (! $base) {
                return $this->response->setJSON(['found' => false]);
            }

            $nombre  = trim("{$base['Nombres']} {$base['apellido_paterno']} {$base['apellido_materno']}");
            $idGrupo = (int) ($base['id_grupo'] ?? 0);

            // Caso A: tiene grupo válido → JOINs para datos oficiales
            if ($idGrupo > 0) {
                $row = $db->table('alumnos_datos_personales adp')
                          ->select('lic.licenciaturas AS carrera,
                                    gm.modalidad, gm.cuatrisem, gm.generacion, gm.id_inter')
                          ->join('grupos_modalidad gm', 'gm.id_grupos = adp.id_grupo', 'left')
                          ->join('licenciaturas lic', 'lic.id = gm.licenciatura', 'left')
                          ->where('adp.numero_control', $numControl)
                          ->get()->getRowArray();

                $idInter     = (int) ($row['id_inter'] ?? 0);
                $tipoPeriodo = match ($idInter) {
                    1 => 'Normal',
                    2 => 'Inter',
                    default => null,
                };
                $interLabel  = match ($idInter) {
                    1 => 'Normal',
                    2 => 'Intercuatrisemestral',
                    default => null,
                };

                return $this->response->setJSON([
                    'found'        => true,
                    'nombre'       => $nombre,
                    'carrera'      => $row['carrera']    ?? null,
                    'modalidad'    => $row['modalidad']  ?? null,
                    'cuatrisem'    => $row['cuatrisem']  ?? null,
                    'generacion'   => $row['generacion'] ?? null,
                    'tipo_periodo' => $tipoPeriodo,
                    'inter_label'  => $interLabel,
                    'editable'     => false,
                ]);
            }

            // Caso B: id_grupo = 0 → datos locales, campos editables
            return $this->response->setJSON([
                'found'        => true,
                'nombre'       => $nombre,
                'carrera'      => $base['Licenciatura'] ?? null,
                'modalidad'    => $base['modalidad']    ?? null,
                'cuatrisem'    => null,
                'generacion'   => null,
                'tipo_periodo' => 'Normal',
                'editable'     => true,
            ]);
        }

        if ($nivel === 'prepa') {
            $db   = \Config\Database::connect('prepa');
            $base = $db->table('alumno_datos')
                       ->select('nombres, apellido_paterno, apellido_materno, id_grupo')
                       ->where('numero_control', $numControl)
                       ->get()->getRowArray();

            if (! $base) {
                return $this->response->setJSON(['found' => false]);
            }

            $nombre  = trim("{$base['nombres']} {$base['apellido_paterno']} {$base['apellido_materno']}");
            $idGrupo = (int) ($base['id_grupo'] ?? 0);

            if ($idGrupo > 0) {
                try {
                    $row = $db->table('grupos_modalidad')
                              ->select('cuatrisem, modalidad')
                              ->where('id_grupos', $idGrupo)
                              ->get()->getRowArray();
                } catch (\Throwable $e) {
                    log_message('error', '[buscarAlumno prepa] ' . $e->getMessage());
                    $row = null;
                }

                return $this->response->setJSON([
                    'found'     => true,
                    'nombre'    => $nombre,
                    'modalidad' => $row['modalidad'] ?? null,
                    'semestre'  => $row['cuatrisem']  ?? null,
                    'editable'  => $row === null,
                ]);
            }

            // id_grupo = 0 → selección manual
            return $this->response->setJSON([
                'found'     => true,
                'nombre'    => $nombre,
                'modalidad' => null,
                'semestre'  => null,
                'editable'  => true,
            ]);
        }

        if ($nivel === 'posgrado') {
            $db  = \Config\Database::connect('uni');
            $row = $db->table('alumnos_datos_personales adp')
                      ->select('adp.Nombres, adp.apellido_paterno, adp.apellido_materno,
                                lic.id AS clavelicen, lic.licenciaturas AS programa,
                                gm.cuatrisem, gm.generacion')
                      ->join('grupos_modalidad gm', 'gm.id_grupos = adp.id_grupo', 'left')
                      ->join('licenciaturas lic', 'lic.id = gm.licenciatura', 'left')
                      ->where('adp.numero_control', $numControl)
                      ->get()->getRowArray();

            if (! $row) {
                return $this->response->setJSON(['found' => false, 'external' => true]);
            }

            $materias = [];
            if (! empty($row['clavelicen'])) {
                $matRows = $db->table('materias')
                              ->select('materia, clavemateria')
                              ->where('clavelicen', $row['clavelicen'])
                              ->orderBy('id', 'ASC')
                              ->get()->getResultArray();

                if (! empty($matRows)) {
                    $matNombres  = array_column($matRows, 'materia');
                    $dbApp       = \Config\Database::connect();
                    $pagadasRows = $dbApp->table('pagos')
                                        ->select('detalle_tramite')
                                        ->where('num_control', $numControl)
                                        ->where('nivel', 'posgrado')
                                        ->where('concepto', 'mensualidad')
                                        ->whereIn('detalle_tramite', $matNombres)
                                        ->get()->getResultArray();
                    $pagadasSet  = array_fill_keys(array_column($pagadasRows, 'detalle_tramite'), true);

                    foreach ($matRows as $m) {
                        $materias[] = [
                            'nombre' => $m['materia'],
                            'clave'  => $m['clavemateria'] ?? '',
                            'pagada' => isset($pagadasSet[$m['materia']]),
                        ];
                    }
                }
            }

            $programa  = $row['programa'] ?? '';
            $modalidad = null;
            if (mb_stripos($programa, 'maestr') !== false) {
                $modalidad = 'Maestría';
            } elseif (mb_stripos($programa, 'doctor') !== false) {
                $modalidad = 'Doctorado';
            }

            return $this->response->setJSON([
                'found'      => true,
                'nombre'     => trim("{$row['Nombres']} {$row['apellido_paterno']} {$row['apellido_materno']}"),
                'carrera'    => $programa,
                'modalidad'  => $modalidad,
                'cuatrisem'  => $row['cuatrisem'] ?? null,
                'generacion' => $row['generacion'] ?? null,
                'materias'   => $materias,
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
        $tipoPeriodo   = $this->request->getPost('tipo_periodo')    ?: null;
        $fechaPagoReal = $this->request->getPost('fecha_pago_real') ?: null;
        $mesesPago     = $this->request->getPost('meses_pago')      ?? [];

        if ($concepto !== 'mensualidad') {
            $fechaPagoReal = null;
        }

        // ── Ruta de mensualidad multi-mes (nueva) ───────────────────
        if ($concepto === 'mensualidad' && ! empty($mesesPago)) {
            return $this->registrarMensualidades($mesesPago, $fechaPagoReal);
        }

        // ── Ruta existente (inscripcion, reinscripcion, tramite, mensualidad legacy) ─
        $periodoNum = $this->request->getPost('periodo_pago') ?: null;

        $errorValidacion = $this->validarSecuenciaPago(
            $concepto,
            $this->request->getPost('num_control'),
            $this->request->getPost('nivel'),
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
            'num_control'      => $this->request->getPost('num_control'),
            'nivel'            => $this->request->getPost('nivel'),
            'nombre_alumno'    => $this->request->getPost('nombre_alumno'),
            'modalidad'        => $this->request->getPost('modalidad') ?: null,
            'carrera'          => $this->request->getPost('carrera') ?: null,
            'concepto'         => $concepto,
            'detalle_tramite'  => $this->request->getPost('detalle_tramite') ?: null,
            'periodo_pago'     => $periodoNum !== null ? (int) $periodoNum : null,
            'tipo_periodo'     => $tipoPeriodo,
            'fecha_pago_real'  => $fechaPagoReal,
            'monto'            => $this->request->getPost('monto'),
            'id_cajero'        => service('session')->get('id_usuario'),
            'metodo_pago'      => $this->request->getPost('metodo_pago') ?: 'Efectivo',
            'observaciones'    => $this->request->getPost('observaciones') ?: null,
            'mes_inicio_ciclo' => in_array($concepto, ['inscripcion', 'reinscripcion'])
                                  ? ($this->request->getPost('mes_inicio_ciclo') ?: null)
                                  : null,
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

    // ── Registro multi-mes: crea un registro por cada mes seleccionado ─

    private function registrarMensualidades(array $mesesPago, ?string $fechaPagoReal): \CodeIgniter\HTTP\ResponseInterface
    {
        $numControl      = $this->request->getPost('num_control');
        $nivel           = $this->request->getPost('nivel');
        $anioMensualidad = (int) ($this->request->getPost('anio_mensualidad') ?: date('Y'));
        $numAbono        = $this->request->getPost('num_abono') ? (int) $this->request->getPost('num_abono') : null;
        $monto           = $this->request->getPost('monto');
        $montosPago      = $this->request->getPost('montos_pago') ?? [];

        // Validar que ningún mes seleccionado tenga ya un pago completo
        $db = \Config\Database::connect();
        $mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                         'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        foreach ($mesesPago as $mes) {
            $mes = (int) $mes;

            // Solo bloquear duplicado cuando es pago completo (sin abono)
            if ($numAbono === null) {
                $existe = $db->table('pagos')
                    ->where('num_control', $numControl)
                    ->where('nivel', $nivel)
                    ->where('concepto', 'mensualidad')
                    ->where('periodo_pago', $mes)
                    ->where('num_abono IS NULL', null, false)
                    ->where("COALESCE(anio_mensualidad, YEAR(fecha_pago_real)) = {$anioMensualidad}", null, false)
                    ->countAllResults();

                if ($existe > 0) {
                    $nomMes = $mesesNombres[$mes - 1] ?? "Mes {$mes}";
                    if ($this->request->isAJAX()) {
                        return $this->response->setStatusCode(422)->setJSON([
                            'success' => false,
                            'message' => "{$nomMes} {$anioMensualidad} ya tiene un pago completo registrado.",
                        ]);
                    }
                    session()->setFlashdata('error', "{$nomMes} {$anioMensualidad} ya tiene un pago completo registrado.");
                    return redirect()->to(base_url('pagos'));
                }
            }
        }

        $model      = new PagoModel();
        $folioLote  = count($mesesPago) > 1
            ? 'L' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8))
            : null;

        $baseData = [
            'num_control'      => $numControl,
            'nivel'            => $nivel,
            'nombre_alumno'    => $this->request->getPost('nombre_alumno'),
            'modalidad'        => $this->request->getPost('modalidad') ?: null,
            'carrera'          => $this->request->getPost('carrera') ?: null,
            'concepto'         => 'mensualidad',
            'detalle_tramite'  => null,
            'tipo_periodo'     => null,
            'fecha_pago_real'  => $fechaPagoReal,
            'anio_mensualidad' => $anioMensualidad,
            'num_abono'        => $numAbono,
            'id_cajero'        => service('session')->get('id_usuario'),
            'metodo_pago'      => $this->request->getPost('metodo_pago') ?: 'Efectivo',
            'observaciones'    => $this->request->getPost('observaciones') ?: null,
            'mes_inicio_ciclo' => null,
            'folio_lote'       => $folioLote,
        ];

        $primerFolio = null;

        foreach ($mesesPago as $i => $mes) {
            $montoMes = isset($montosPago[$i]) && $montosPago[$i] !== ''
                ? (float) $montosPago[$i]
                : (float) $monto;
            $data = array_merge($baseData, ['periodo_pago' => (int) $mes, 'monto' => $montoMes]);

            if (! $model->insert($data)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Error al guardar el pago. Intente de nuevo.',
                    ]);
                }
                session()->setFlashdata('error', 'Error al guardar el pago. Intente de nuevo.');
                return redirect()->to(base_url('pagos'));
            }

            $insertId     = $model->getInsertID();
            $inserted     = $model->find($insertId);
            $folio        = date('Ymd') . '-' . $insertId . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $sello        = hash('sha256', $folio . $inserted['created_at'] . 'LLAVE_SECRETA');

            $model->update($insertId, ['folio_digital' => $folio, 'sello_digital' => $sello]);

            if ($primerFolio === null) {
                $primerFolio = $folio;
            }
        }

        $pdfUrl = $folioLote !== null
            ? base_url('pagos/comprobante-lote/' . $folioLote)
            : base_url('pagos/comprobante/' . $primerFolio);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'       => true,
                'folio_digital' => $primerFolio,
                'folio_lote'    => $folioLote,
                'pdf_url'       => $pdfUrl,
                'csrf_name'     => csrf_token(),
                'csrf_hash'     => csrf_hash(),
            ]);
        }

        return redirect()->to($pdfUrl);
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
        } elseif ($pago['nivel'] === 'posgrado' && $pago['concepto'] === 'mensualidad') {
            $conceptoLabel = mb_stripos($pago['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
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
                $anio           = $pago['anio_mensualidad'] ?? date('Y', strtotime($pago['created_at']));
                $periodoDisplay = $mesNombre . ' ' . $anio;
                if (! empty($pago['num_abono'])) {
                    $periodoDisplay .= ' — Abono ' . $pago['num_abono'];
                }
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

    // ── Comprobante multi-mes (lote) ─────────────────────────────────────────────

    public function comprobanteLote(string $lote)
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $model = new PagoModel();
        $pagos = $model
            ->where('folio_lote', $lote)
            ->orderBy('anio_mensualidad', 'ASC')
            ->orderBy('periodo_pago', 'ASC')
            ->findAll();

        if (empty($pagos)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Lote no encontrado: {$lote}");
        }

        if (count($pagos) === 1) {
            return $this->comprobante($pagos[0]['folio_digital']);
        }

        $pago   = $pagos[0];
        $db     = \Config\Database::connect();
        $cajero = $db->table('usuarios')
                     ->select('nombre')
                     ->where('id', $pago['id_cajero'])
                     ->get()->getRowArray();

        $mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                         'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        $mesesDisplay = [];
        $montoTotal   = 0;
        foreach ($pagos as $p) {
            $mesNom         = $mesesNombres[((int) $p['periodo_pago']) - 1] ?? '?';
            $anioP          = $p['anio_mensualidad'] ?? date('Y', strtotime($p['created_at']));
            $mesesDisplay[] = $mesNom . ' ' . $anioP;
            $montoTotal    += (float) $p['monto'];
        }

        $niveles   = ['uni' => 'Universidad', 'prepa' => 'Preparatoria', 'posgrado' => 'Posgrado'];
        $logoMap   = ['uni' => 'logosuni.jpeg', 'prepa' => 'logosba.jpeg', 'posgrado' => 'logosuni.jpeg'];
        $logoFile  = FCPATH . 'assets/img/' . ($logoMap[$pago['nivel']] ?? '');
        $logoBase64 = null;
        if (file_exists($logoFile)) {
            $ext        = pathinfo($logoFile, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        $viewData = [
            'pagos'         => $pagos,
            'pago'          => $pago,
            'mesesDisplay'  => implode(', ', $mesesDisplay),
            'montoTotal'    => $montoTotal,
            'nivelLabel'    => $niveles[$pago['nivel']] ?? $pago['nivel'],
            'fechaHora'     => date('d/m/Y H:i:s', strtotime($pago['created_at'])),
            'montoFormato'  => '$' . number_format($montoTotal, 2),
            'montoLetras'   => $this->numeroALetras($montoTotal),
            'nombreCajero'  => $cajero['nombre'] ?? 'N/D',
            'logoBase64'    => $logoBase64,
            'folio_lote'    => $lote,
        ];

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pagos/comprobante_lote_pdf', $viewData));
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="comprobante-lote-' . $lote . '.pdf"')
            ->setBody($dompdf->output());
    }

    // ────────────────────────────────────────────────────────────────────────────

    private function validarSecuenciaPago(
        string  $concepto,
        ?string $numControl,
        ?string $nivel,
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
            // Sin validación de inscripción previa: permite alumnos avanzados o de transferencia.
            // Sin validación de secuencia de periodos: el cajero selecciona el periodo correcto.
        }

        if ($concepto === 'mensualidad') {
            $anioActual = (int) date('Y');

            // Reinscripción del año vigente (ancla prioritaria)
            $pagoInicial = $db->table('pagos')
                ->where('num_control', $numControl)
                ->where('nivel', $nivel)
                ->where('concepto', 'reinscripcion')
                ->where('YEAR(created_at)', $anioActual)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            // Inscripción del año vigente (respaldo)
            if (! $pagoInicial) {
                $pagoInicial = $db->table('pagos')
                    ->where('num_control', $numControl)
                    ->where('nivel', $nivel)
                    ->where('concepto', 'inscripcion')
                    ->where('YEAR(created_at)', $anioActual)
                    ->orderBy('id', 'ASC')
                    ->limit(1)
                    ->get()->getRowArray();
            }

            // Sin pago inicial del ciclo vigente → mensualidad directa (permitida sin ancla)
            if (! $pagoInicial) {
                return null;
            }

            if ($fechaPagoReal) {
                $mesReal  = (int) date('n', strtotime($pagoInicial['created_at']));
                $anioRef  = (int) date('Y', strtotime($pagoInicial['created_at']));
                $mesAncla = ! empty($pagoInicial['mes_inicio_ciclo'])
                            ? (int) $pagoInicial['mes_inicio_ciclo']
                            : $mesReal;
                if ($mesAncla > $mesReal) {
                    $anioRef--;
                }

                $mesMens  = (int) date('n', strtotime($fechaPagoReal));
                $anioMens = (int) date('Y', strtotime($fechaPagoReal));

                $tsAncla = mktime(0, 0, 0, $mesAncla, 1, $anioRef);
                $tsMens  = mktime(0, 0, 0, $mesMens,  1, $anioMens);

                if ($tsMens < $tsAncla) {
                    return 'No se puede registrar una mensualidad anterior al inicio del ciclo '
                        . '(' . $meses[$mesAncla - 1] . ' ' . $anioRef . ').';
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
