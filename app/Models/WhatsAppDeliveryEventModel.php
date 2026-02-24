<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsAppDeliveryEventModel extends Model
{
    protected $table = 'whatsapp_delivery_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'provider_event_id',
        'event_type',
        'payload_json',
        'processed_at',
    ];

    protected $useTimestamps = false;
}
