<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDetalleTramiteToVarchar extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('pagos', [
            'detalle_tramite' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('pagos', [
            'detalle_tramite' => [
                'type'       => 'ENUM',
                'constraint' => ['constancia', 'constancia_ext', 'historial', 'gafete'],
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }
}
