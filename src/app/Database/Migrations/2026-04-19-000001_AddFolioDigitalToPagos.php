<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFolioDigitalToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'folio_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('ALTER TABLE pagos ADD UNIQUE INDEX uq_folio_digital (folio_digital)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE pagos DROP INDEX uq_folio_digital');
        $this->forge->dropColumn('pagos', 'folio_digital');
    }
}
