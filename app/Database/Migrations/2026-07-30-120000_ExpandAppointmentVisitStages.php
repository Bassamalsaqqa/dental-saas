<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandAppointmentVisitStages extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE `appointments` MODIFY `status` "
            . "ENUM('scheduled','confirmed','arrived','ready','in_chair','checkout','completed','cancelled','no_show') "
            . "NULL DEFAULT 'scheduled'"
        );
    }

    public function down()
    {
        $this->db->query(
            "UPDATE `appointments` SET `status` = CASE "
            . "WHEN `status` IN ('arrived','ready','in_chair','checkout') THEN 'confirmed' "
            . "ELSE `status` END"
        );
        $this->db->query(
            "ALTER TABLE `appointments` MODIFY `status` "
            . "ENUM('scheduled','confirmed','completed','cancelled','no_show') "
            . "NULL DEFAULT 'scheduled'"
        );
    }
}
