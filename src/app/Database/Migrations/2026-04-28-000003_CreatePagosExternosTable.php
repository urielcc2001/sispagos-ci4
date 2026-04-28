<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosExternosTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'folio_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'nombre_cliente' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nivel' => [
                'type'       => 'ENUM',
                'constraint' => ['uni', 'prepa', 'posgrado'],
            ],
            'modalidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'concepto' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'metodo_pago' => [
                'type'       => 'ENUM',
                'constraint' => ['Efectivo', 'Transferencia'],
                'default'    => 'Efectivo',
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'id_cajero' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('pagos_externos');
    }

    public function down(): void
    {
        $this->forge->dropTable('pagos_externos');
    }
}
