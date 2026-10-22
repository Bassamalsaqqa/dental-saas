<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicModel extends Model
{
    protected $table = 'clinics';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'uuid', 'domain', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    /** Physical offboarding requires a separately reviewed preservation/purge contract. */
    public function delete($id = null, bool $purge = false)
    {
        return false;
    }
}
