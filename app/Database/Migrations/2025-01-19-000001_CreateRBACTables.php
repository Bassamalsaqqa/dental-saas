<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRBACTables extends Migration
{
    public function up()
    {
        // 1. Create permissions table
        if (!$this->db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'module' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'action' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                ],
            ]);
            
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['module', 'action'], 'unique_permission');
            $this->forge->createTable('permissions', true);
        }

        // 2. Create roles table (extend existing groups or create new)
        if (!$this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                ],
            ]);
            
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('roles', true);
        }

        // 3. Create role_permissions table
        if (!$this->db->tableExists('role_permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'permission_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'granted' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['role_id', 'permission_id'], 'unique_role_permission');
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('role_permissions', true);
        }

        // 4. Create user_roles table (if not using existing users_groups)
        if (!$this->db->tableExists('user_roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'assigned_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'assigned_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
            
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['user_id', 'role_id'], 'unique_user_role');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('assigned_by', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('user_roles', true);
        }

        // 5. Create user_permissions table (for individual overrides)
        if (!$this->db->tableExists('user_permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'permission_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'granted' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'expires_at' => [
                    'type' => 'TIMESTAMP',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
            
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['user_id', 'permission_id'], 'unique_user_permission');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('user_permissions', true);
        }
    }

    public function down()
    {
        // We do NOT drop tables in down() if we didn't create them, 
        // but for now, standard down is fine as long as we know what we're doing.
        // In a "fix" migration, safe down might be empty.
    }
}