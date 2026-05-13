<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeaveBalancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'type_conge_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'annee' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 2026,
            ],
            'jours_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 25,
            ],
            'jours_pris' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
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
        $this->forge->addKey(['employe_id', 'type_conge_id', 'annee']);
        $this->forge->createTable('leave_balance');
    }

    public function down()
    {
        $this->forge->dropTable('leave_balance');
    }
}
