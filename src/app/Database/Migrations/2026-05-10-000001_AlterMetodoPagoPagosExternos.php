<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMetodoPagoPagosExternos extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('pagos_externos', [
            'metodo_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
                'default'    => 'Efectivo',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('pagos_externos', [
            'metodo_pago' => [
                'type'       => 'ENUM',
                'constraint' => ['Efectivo', 'Transferencia'],
                'default'    => 'Efectivo',
            ],
        ]);
    }
}
