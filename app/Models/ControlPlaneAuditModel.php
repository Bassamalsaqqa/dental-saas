<?php

namespace App\Models;

use CodeIgniter\Model;

class ControlPlaneAuditModel extends Model
{
    protected $table = 'control_plane_audits';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $allowedFields = [
        'actor_user_id',
        'event_type',
        'route',
        'method',
        'ip',
        'user_agent',
        'metadata_json',
        'created_at',
    ];
}
