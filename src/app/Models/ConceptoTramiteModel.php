<?php

namespace App\Models;

use CodeIgniter\Model;

class ConceptoTramiteModel extends Model
{
    protected $table         = 'conceptos_tramites';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nombre_tramite', 'precio_sugerido', 'nivel', 'estatus'];

    public function activosPorNivel(string $nivel): array
    {
        $builder = $this->where('estatus', 'activo');

        if ($nivel === 'uni') {
            $builder->groupStart()
                ->where('nivel', 'universidad')
                ->orWhere('nivel', 'ambos')
                ->groupEnd();
        } elseif ($nivel === 'prepa') {
            $builder->groupStart()
                ->where('nivel', 'bachillerato')
                ->orWhere('nivel', 'ambos')
                ->groupEnd();
        }

        return $builder->orderBy('nombre_tramite')->findAll();
    }
}
