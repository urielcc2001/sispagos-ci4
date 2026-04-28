<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoExternoModel extends Model
{
    protected $table         = 'pagos_externos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'folio_digital', 'sello_digital', 'nombre_cliente', 'nivel', 'modalidad',
        'concepto', 'monto', 'metodo_pago', 'observaciones', 'id_cajero',
    ];
}
