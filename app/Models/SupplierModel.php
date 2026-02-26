<?php

namespace App\Models;

class SupplierModel extends TenantAwareModel
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'whatsapp',
        'address',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]',
        'contact_person' => 'permit_empty|max_length[150]',
        'phone' => 'permit_empty|max_length[50]',
        'email' => 'permit_empty|valid_email|max_length[150]',
        'whatsapp' => 'permit_empty|max_length[50]',
        'address' => 'permit_empty|max_length[255]',
        'notes' => 'permit_empty',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public function getActiveOptionsByClinic(int $clinicId): array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findByClinicAndName(int $clinicId, string $name): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('name', trim($name))
            ->first();
    }
}

