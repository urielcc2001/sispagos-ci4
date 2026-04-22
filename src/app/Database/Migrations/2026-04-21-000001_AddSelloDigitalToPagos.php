<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSelloDigitalToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'sello_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'folio_digital',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pagos', 'sello_digital');
    }
}
