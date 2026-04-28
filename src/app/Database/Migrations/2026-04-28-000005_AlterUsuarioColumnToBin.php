<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsuarioColumnToBin extends Migration
{
    public function up()
    {
        // utf8mb4_bin hace la comparación case-sensitive (Admin ≠ admin)
        $this->db->query(
            'ALTER TABLE usuarios MODIFY COLUMN usuario VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL'
        );
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE usuarios MODIFY COLUMN usuario VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL'
        );
    }
}