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
        'folio_digital', 'sello_digital', 'num_control', 'nivel', 'nombre_alumno', 'modalidad',
        'carrera', 'concepto', 'detalle_tramite', 'periodo_pago', 'tipo_periodo', 'fecha_pago_real',
        'monto', 'id_cajero',
    ];
}
