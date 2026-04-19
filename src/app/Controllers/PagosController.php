<?php

namespace App\Controllers;

use App\Models\PagoModel;

class PagosController extends BaseController
{
    public function index()
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        return view('pagos/registro');
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
            'monto'           => $this->request->getPost('monto'),
            'id_cajero'       => service('session')->get('id_usuario'),
        ];

        $model = new PagoModel();

        if (! $model->insert($data)) {
            return view('pagos/registro', ['error' => 'Error al guardar el pago. Intente de nuevo.']);
        }

        return redirect()->to(base_url('pagos'))->with('success', '¡Pago registrado correctamente!');
    }
}
