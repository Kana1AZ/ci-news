<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserRole extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'default' => 'user', // Default role as 'user'
                'after' => 'bio' // Position it after the 'bio' column if it exists
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role');
    }
}
