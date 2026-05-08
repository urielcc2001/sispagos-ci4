<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAbonoYearLoteToPagos extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pagos', [
            'folio_lote' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'folio_digital',
            ],
            'anio_mensualidad' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'fecha_pago_real',
            ],
            'num_abono' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'anio_mensualidad',
            ],
        ]);

        $this->db->query('ALTER TABLE pagos ADD INDEX idx_folio_lote (folio_lote)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE pagos DROP INDEX IF EXISTS idx_folio_lote');
        $this->forge->dropColumn('pagos', ['folio_lote', 'anio_mensualidad', 'num_abono']);
    }
}
