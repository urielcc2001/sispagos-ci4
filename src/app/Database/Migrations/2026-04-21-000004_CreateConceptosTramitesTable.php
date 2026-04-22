<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConceptosTramitesTable extends Migration
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
            'nombre_tramite' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'precio_sugerido' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'nivel' => [
                'type'       => 'ENUM',
                'constraint' => ['bachillerato', 'universidad', 'ambos'],
                'default'    => 'ambos',
            ],
            'estatus' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo'],
                'default'    => 'activo',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('conceptos_tramites');

        // Datos iniciales equivalentes a los hardcodeados anteriores
        $this->db->table('conceptos_tramites')->insertBatch([
            ['nombre_tramite' => 'Constancia Escolar',             'precio_sugerido' => 150.00, 'nivel' => 'ambos',       'estatus' => 'activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nombre_tramite' => 'Constancia Extranjero',          'precio_sugerido' =>  50.00, 'nivel' => 'ambos',       'estatus' => 'activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nombre_tramite' => 'Historial de Calificaciones',    'precio_sugerido' => 150.00, 'nivel' => 'ambos',       'estatus' => 'activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['nombre_tramite' => 'Gafete',                         'precio_sugerido' =>  30.00, 'nivel' => 'ambos',       'estatus' => 'activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('conceptos_tramites');
    }
}
