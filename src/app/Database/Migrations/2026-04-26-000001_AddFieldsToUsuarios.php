<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToUsuarios extends Migration
{
    public function up()
    {
        $fields = [
            'rfc' => [
                'type'       => 'VARCHAR',
                'constraint' => 13,
                'null'       => true,
                'after'      => 'nombre',
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'rfc',
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'correo',
            ],
        ];

        $this->forge->addColumn('usuarios', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', ['rfc', 'correo', 'status']);
    }
}
