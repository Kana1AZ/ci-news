<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyExpirationDateToOnlyDate extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('posts', [
            'expiration_date' => [
                'type' => 'DATE',
                'null' => true,  // Assuming it's okay to have null values
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('posts', [
            'expiration_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }
}


