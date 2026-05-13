<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDemandeCodePromoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_demande_code_promo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_utilisateur' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'code_saisi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'statut' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'en_attente',
            ],
            'id_admin_traitement' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'note_admin' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'date_demande' => [
                'type' => 'DATETIME',
            ],
            'date_traitement' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_demande_code_promo', true);
        $this->forge->addKey('id_utilisateur');
        $this->forge->addKey('statut');
        $this->forge->addForeignKey('id_utilisateur', 'utilisateur', 'id_utilisateur', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_admin_traitement', 'utilisateur', 'id_utilisateur', 'SET NULL', 'CASCADE');
        $this->forge->createTable('demande_code_promo');
    }

    public function down()
    {
        $this->forge->dropTable('demande_code_promo', true);
    }
}
