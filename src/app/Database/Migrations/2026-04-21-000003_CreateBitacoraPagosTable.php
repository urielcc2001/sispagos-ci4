<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBitacoraPagosTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pago' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'folio_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'id_admin' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'accion' => [
                'type'       => 'ENUM',
                'constraint' => ['edicion', 'eliminacion'],
            ],
            'detalle' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('id_pago');
        $this->forge->addKey('id_admin');
        $this->forge->createTable('bitacora_pagos');
    }

    public function down(): void
    {
        $this->forge->dropTable('bitacora_pagos');
    }
}
