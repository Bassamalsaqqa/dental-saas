<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClinicsAndMemberships extends Migration
{
    public function up()
    {
        // 1. Create clinics table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'suspended'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('clinics');

        // 2. Create clinic_users table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT', // IonAuth users id is likely INT(11) unsigned
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        // Assuming 'users' and 'roles' tables exist from previous setup/dumps.
        // We add foreign keys loosely or strictly depending on whether those tables are managed by migrations or dumps.
        // Given project state, users/roles are in legacy dump, so strict FK might fail if tables aren't MyISAM vs InnoDB compatible or created yet in test env.
        // However, standard practice is to add them. I will assume InnoDB/compatible.
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->addUniqueKey(['user_id', 'clinic_id']);
        $this->forge->createTable('clinic_users');
    }

    public function down()
    {
        $this->forge->dropTable('clinic_users');
        $this->forge->dropTable('clinics');
    }
}