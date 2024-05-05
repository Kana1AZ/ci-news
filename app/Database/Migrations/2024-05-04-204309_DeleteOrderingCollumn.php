<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeleteOrderingCollumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('categories', 'ordering');
    }

    public function down()
    {
        $this->forge->addColumn('categories', [
            'ordering' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 10000,
            ],
        ]);
    }
}