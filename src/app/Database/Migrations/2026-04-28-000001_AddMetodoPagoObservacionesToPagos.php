<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetodoPagoObservacionesToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'metodo_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'Efectivo',
                'after'      => 'monto',
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'metodo_pago',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos', 'observaciones');
        $this->forge->dropColumn('pagos', 'metodo_pago');
    }
}