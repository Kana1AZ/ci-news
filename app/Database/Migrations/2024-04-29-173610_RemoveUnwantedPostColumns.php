<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUnwantedColumns extends Migration
{
    public function up()
    {
        // Remove columns
        $this->forge->dropColumn('posts', 'slug');
        $this->forge->dropColumn('posts', 'tags');
        $this->forge->dropColumn('posts', 'meta_keywords');
        $this->forge->dropColumn('posts', 'meta_description');
    }

    public function down()
    {
        // Add columns back if necessary
        $fields = [
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tags' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'meta_keywords' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'meta_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('posts', $fields);
    }
}
