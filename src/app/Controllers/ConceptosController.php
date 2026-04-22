<?php

namespace App\Controllers;

use App\Models\ConceptoTramiteModel;

class ConceptosController extends BaseController
{
    private function checkAdmin(): mixed
    {
        $session = service('session');

        if (! $session->get('logged_in') || $session->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        return null;
    }

    public function index()
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $model     = new ConceptoTramiteModel();
        $conceptos = $model->orderBy('nivel')->orderBy('nombre_tramite')->findAll();

        return view('admin/conceptos_tramites', ['conceptos' => $conceptos]);
    }

    public function guardar()
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $model = new ConceptoTramiteModel();

        $model->insert([
            'nombre_tramite'  => trim($this->request->getPost('nombre_tramite')),
            'precio_sugerido' => $this->request->getPost('precio_sugerido'),
            'nivel'           => $this->request->getPost('nivel'),
            'estatus'         => 'activo',
        ]);

        return redirect()->to(base_url('admin/conceptos'))
            ->with('success', 'Trámite agregado correctamente.');
    }

    public function actualizar(int $id)
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $model = new ConceptoTramiteModel();

        $model->update($id, [
            'nombre_tramite'  => trim($this->request->getPost('nombre_tramite')),
            'precio_sugerido' => $this->request->getPost('precio_sugerido'),
            'nivel'           => $this->request->getPost('nivel'),
            'estatus'         => $this->request->getPost('estatus'),
        ]);

        return redirect()->to(base_url('admin/conceptos'))
            ->with('success', 'Trámite actualizado correctamente.');
    }

    public function toggle(int $id)
    {
        if ($guard = $this->checkAdmin()) {
            return $guard;
        }

        $model   = new ConceptoTramiteModel();
        $tramite = $model->find($id);

        if (! $tramite) {
            return redirect()->to(base_url('admin/conceptos'));
        }

        $nuevoEstatus = $tramite['estatus'] === 'activo' ? 'inactivo' : 'activo';
        $model->update($id, ['estatus' => $nuevoEstatus]);

        return redirect()->to(base_url('admin/conceptos'))
            ->with('success', "Trámite marcado como {$nuevoEstatus}.");
    }
}
