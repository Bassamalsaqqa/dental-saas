<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicModel extends Model
{
    protected $table = 'clinics';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}
