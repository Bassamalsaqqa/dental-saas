<?php

namespace App\Models;

class ExaminationNoteAddendumModel extends TenantAwareModel
{
    protected $table = 'examination_note_addenda';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id', 'examination_id', 'signature_id', 'added_by_user_id', 'added_by_name',
        'reason', 'body', 'previous_hash', 'content_hash', 'created_at',
    ];
    protected $useTimestamps = false;
}
