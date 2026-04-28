<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMesInicioCicloToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'mes_inicio_ciclo' => [
                'type'     => 'TINYINT UNSIGNED',
                'null'     => true,
                'default'  => null,
                'after'    => 'fecha_pago_real',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos', 'mes_inicio_ciclo');
    }
}