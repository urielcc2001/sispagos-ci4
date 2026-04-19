<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table         = 'pagos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'num_control', 'nivel', 'nombre_alumno', 'modalidad',
        'carrera', 'concepto', 'detalle_tramite', 'monto', 'id_cajero',
    ];
}
