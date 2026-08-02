<?php

namespace App\Models;

class ExaminationNoteSignatureModel extends TenantAwareModel
{
    protected $table = 'examination_note_signatures';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id', 'examination_id', 'patient_id', 'signed_by_user_id', 'signed_by_name',
        'signed_at', 'attestation_version', 'snapshot_json', 'content_hash', 'created_at',
    ];
    protected $useTimestamps = false;
}
