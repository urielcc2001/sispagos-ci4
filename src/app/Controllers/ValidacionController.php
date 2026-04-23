<?php

namespace App\Controllers;

use App\Models\PagoModel;

class ValidacionController extends BaseController
{
    public function validar(string $sello)
    {
        $model = new PagoModel();
        $pago  = $model->where('sello_digital', $sello)->first();

        if (! $pago) {
            return view('validacion/resultado', ['valido' => false]);
        }

        $conceptos = [
            'inscripcion'   => 'Inscripción',
            'reinscripcion' => 'Reinscripción',
            'mensualidad'   => 'Mensualidad',
            'tramite'       => 'Trámite',
        ];

        return view('validacion/resultado', [
            'valido'        => true,
            'nombre_alumno' => $pago['nombre_alumno'],
            'concepto'      => $conceptos[$pago['concepto']] ?? $pago['concepto'],
            'monto'         => '$' . number_format((float) $pago['monto'], 2),
            'folio'         => $pago['folio_digital'],
            'fecha'         => date('d/m/Y H:i', strtotime($pago['created_at'])),
        ]);
    }
}
