<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosTable extends Migration
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
            'num_control' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nivel' => [
                'type'       => 'ENUM',
                'constraint' => ['uni', 'prepa', 'posgrado'],
            ],
            'nombre_alumno' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'modalidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'carrera' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'concepto' => [
                'type'       => 'ENUM',
                'constraint' => ['inscripcion', 'reinscripcion', 'mensualidad', 'tramite'],
            ],
            'detalle_tramite' => [
                'type'       => 'ENUM',
                'constraint' => ['constancia', 'constancia_ext', 'historial', 'gafete'],
                'null'       => true,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
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
        $this->forge->addKey('num_control');
        $this->forge->createTable('pagos');
    }

    public function down(): void
    {
        $this->forge->dropTable('pagos');
    }
}
