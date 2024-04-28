<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameUserIdToAuthorId extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('categories', [
            'user_id' => [
                'name' => 'author_id',
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ]
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('categories', [
            'author_id' => [
                'name' => 'user_id',
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ]
        ]);
    }
}
