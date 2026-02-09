<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureIonAuthDefaultGroup extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('groups')) {
            return;
        }

        $exists = $this->db->table('groups')
            ->where('name', 'members')
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('groups')->insert([
                'name' => 'members',
                'description' => 'Members',
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('groups')) {
            return;
        }

        $this->db->table('groups')->where('name', 'members')->delete();
    }
}
