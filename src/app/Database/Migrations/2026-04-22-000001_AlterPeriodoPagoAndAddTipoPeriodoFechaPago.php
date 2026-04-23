<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPeriodoPagoAndAddTipoPeriodoFechaPago extends Migration
{
    public function up(): void
    {
        // Cambiar periodo_pago de VARCHAR a INT
        $this->forge->modifyColumn('pagos', [
            'periodo_pago' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
        ]);

        // Agregar tipo_periodo (Normal / Inter)
        $this->forge->addColumn('pagos', [
            'tipo_periodo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'after'      => 'periodo_pago',
            ],
        ]);

        // Agregar fecha_pago_real (solo mensualidades)
        $this->forge->addColumn('pagos', [
            'fecha_pago_real' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
                'after'   => 'tipo_periodo',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos', 'fecha_pago_real');
        $this->forge->dropColumn('pagos', 'tipo_periodo');

        // Revertir periodo_pago a VARCHAR
        $this->forge->modifyColumn('pagos', [
            'periodo_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }
}
