<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $returnType = 'array';

    public function findByUsuario(string $usuario): ?array
    {
        return $this->where('usuario', $usuario)->first();
    }
}
