<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSelloDigitalToPagosExternos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos_externos', [
            'sello_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'folio_digital',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos_externos', 'sello_digital');
    }
}
