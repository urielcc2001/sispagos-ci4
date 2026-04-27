<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function index()
    {
        return view('login');
    }

    public function login()
    {
        $usuario  = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        $model = new UsuarioModel();
        $user  = $model->findByUsuario($usuario);

        if (! $user || ! password_verify($password, $user['password'])) {
            return view('login', ['error' => 'Usuario o contraseña incorrectos.']);
        }

        if (($user['status'] ?? 1) == 0) {
            return view('login', ['error' => 'Tu cuenta está deshabilitada. Contacta al administrador.']);
        }

        $session = service('session');
        $session->set([
            'id_usuario' => $user['id'],
            'nombre'     => $user['nombre'],
            'rol'        => $user['rol'],
            'logged_in'  => true,
        ]);

        if (session()->get('rol') == 'admin') {
            return redirect()->to('/dashboard');
        } else {
            return redirect()->to('/pagos');
        }
    }
}
