<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class ConfiguracionController extends BaseController
{
    private function requireLogin(): mixed
    {
        if (! service('session')->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    private function requireAdmin(): mixed
    {
        $session = service('session');
        if (! $session->get('logged_in') || $session->get('rol') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    // Redirige según rol
    public function index()
    {
        $r = $this->requireLogin();
        if ($r) return $r;

        if (service('session')->get('rol') === 'admin') {
            return redirect()->to(base_url('configuracion/usuarios'));
        }
        return redirect()->to(base_url('configuracion/password'));
    }

    // GET / POST — Cambiar contraseña propia (todos los roles)
    public function cambiarPassword()
    {
        $r = $this->requireLogin();
        if ($r) return $r;

        $session = service('session');
        $data    = ['error' => null, 'success' => null];

        if ($this->request->getMethod() === 'POST') {
            $actual    = $this->request->getPost('password_actual');
            $nueva     = $this->request->getPost('password_nueva');
            $confirmar = $this->request->getPost('password_confirmar');

            $model = new UsuarioModel();
            $user  = $model->find($session->get('id_usuario'));

            if (! $user || ! password_verify($actual, $user['password'])) {
                $data['error'] = 'La contraseña actual es incorrecta.';
            } elseif (strlen($nueva) < 6) {
                $data['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            } elseif ($nueva !== $confirmar) {
                $data['error'] = 'Las contraseñas nuevas no coinciden.';
            } else {
                $model->update($session->get('id_usuario'), [
                    'password' => password_hash($nueva, PASSWORD_DEFAULT),
                ]);
                $data['success'] = 'Contraseña actualizada correctamente.';
            }
        }

        return view('configuracion/cambiar_password', $data);
    }

    // GET — Panel de gestión de usuarios (solo admin)
    public function adminUsuarios()
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        $model    = new UsuarioModel();
        $usuarios = $model->findAll();

        $flash = [
            'success' => service('session')->getFlashdata('success'),
            'error'   => service('session')->getFlashdata('error'),
        ];

        return view('configuracion/admin_usuarios', ['usuarios' => $usuarios, 'flash' => $flash]);
    }

    // POST — Crear usuario (solo admin)
    public function crearUsuario()
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        $nombre  = trim($this->request->getPost('nombre'));
        $rfc     = strtoupper(trim($this->request->getPost('rfc')));
        $rol     = $this->request->getPost('rol');
        $correo  = trim($this->request->getPost('correo'));
        $usuario = trim($this->request->getPost('usuario'));

        if (! $nombre || ! $rfc || ! $rol || ! $usuario) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', 'Todos los campos obligatorios deben completarse.');
        }

        $model = new UsuarioModel();

        if ($model->findByUsuario($usuario)) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', "El nombre de usuario '$usuario' ya existe.");
        }

        $model->insert([
            'nombre'   => $nombre,
            'rfc'      => $rfc,
            'correo'   => $correo,
            'usuario'  => $usuario,
            'password' => password_hash($rfc, PASSWORD_DEFAULT),
            'rol'      => $rol,
            'status'   => 1,
        ]);

        return redirect()->to(base_url('configuracion/usuarios'))
                         ->with('success', "Usuario '$nombre' creado. Contraseña inicial: $rfc");
    }

    // POST — Activar / Desactivar usuario (solo admin)
    public function toggleUsuario(int $id)
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        if ($id === (int) service('session')->get('id_usuario')) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        $model = new UsuarioModel();
        $user  = $model->find($id);

        if ($user) {
            $nuevoStatus = ($user['status'] ?? 1) == 1 ? 0 : 1;
            $model->update($id, ['status' => $nuevoStatus]);
        }

        return redirect()->to(base_url('configuracion/usuarios'));
    }

    // GET — Datos de un usuario en JSON (para poblar modal de edición)
    public function editarUsuario(int $id)
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        $model = new UsuarioModel();
        $user  = $model->find($id);

        if (! $user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado.']);
        }

        return $this->response->setJSON([
            'id'      => $user['id'],
            'nombre'  => $user['nombre'],
            'usuario' => $user['usuario'],
            'rfc'     => $user['rfc'] ?? '',
            'correo'  => $user['correo'] ?? '',
            'rol'     => $user['rol'],
        ]);
    }

    // POST — Actualizar nombre, usuario y RFC (solo admin; no toca contraseña ni rol)
    public function actualizarUsuario(int $id)
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        $model = new UsuarioModel();

        if (! $model->find($id)) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', 'Usuario no encontrado.');
        }

        $nombre  = trim($this->request->getPost('nombre'));
        $usuario = trim($this->request->getPost('usuario'));
        $rfc     = strtoupper(trim($this->request->getPost('rfc')));

        if (! $nombre || ! $usuario) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', 'Nombre y usuario son obligatorios.');
        }

        $existente = $model->where('usuario', $usuario)->where('id !=', $id)->first();
        if ($existente) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', "El nombre de usuario '$usuario' ya está en uso.");
        }

        $model->update($id, [
            'nombre'  => $nombre,
            'usuario' => $usuario,
            'rfc'     => $rfc ?: null,
        ]);

        return redirect()->to(base_url('configuracion/usuarios'))
                         ->with('success', "Usuario '{$nombre}' actualizado correctamente.");
    }

    // POST — Restablecer contraseña al RFC (solo admin)
    public function resetearPassword(int $id)
    {
        $r = $this->requireAdmin();
        if ($r) return $r;

        $model = new UsuarioModel();
        $user  = $model->find($id);

        if (! $user || empty($user['rfc'])) {
            return redirect()->to(base_url('configuracion/usuarios'))
                             ->with('error', 'No se pudo restablecer: el usuario no tiene RFC registrado.');
        }

        $model->update($id, [
            'password' => password_hash(strtoupper($user['rfc']), PASSWORD_DEFAULT),
        ]);

        return redirect()->to(base_url('configuracion/usuarios'))
                         ->with('success', "Contraseña de '{$user['nombre']}' restablecida a su RFC.");
    }
}
