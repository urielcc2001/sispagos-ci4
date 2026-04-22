<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeriodoPagoToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'periodo_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'sello_digital',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos', 'periodo_pago');
    }
}
