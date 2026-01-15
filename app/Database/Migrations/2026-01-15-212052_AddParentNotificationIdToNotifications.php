<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentNotificationIdToNotifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notifications', [
            'parent_notification_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'job_audit_id'
            ]
        ]);
        
        // Optional: Add index for parent lookup if needed for UI hierarchy
        $this->forge->addKey('parent_notification_id');
        // Note: processIndexes() is needed if using addKey after createTable, but addColumn might not support it directly 
        // in all drivers efficiently without separate query. 
        // For simple addColumn, CI4 usually handles basic schema changes. 
        // Adding index explicitly via raw SQL or separateforge call is safer for existing tables if addColumn doesn't do it.
        // Let's stick to just the column for now, indexes can be added if performance demands.
    }

    public function down()
    {
        $this->forge->dropColumn('notifications', 'parent_notification_id');
    }
}