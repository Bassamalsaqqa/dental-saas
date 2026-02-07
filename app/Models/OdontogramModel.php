<?php

namespace App\Models;

use CodeIgniter\Model;

class OdontogramModel extends TenantAwareModel
{
    protected $table = 'odontograms';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'examination_id',
        'tooth_number',
        'tooth_position',
        'condition_type',
        'condition_description',
        'treatment_notes',
        'treatment_date',
        'treatment_status',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'tooth_number' => 'required|integer|greater_than[0]|less_than[33]',
        'tooth_position' => 'required|in_list[upper_right,upper_left,lower_left,lower_right]',
        'condition_type' => 'required|in_list[healthy,cavity,filling,crown,root_canal,extracted,implant,bridge,partial_denture,full_denture,other]',
        'treatment_status' => 'required|in_list[pending,in_progress,completed,needs_attention]'
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'tooth_number' => [
            'required' => 'Tooth number is required',
            'integer' => 'Tooth number must be a valid number',
            'greater_than' => 'Tooth number must be greater than 0',
            'less_than' => 'Tooth number must be less than 33'
        ],
        'tooth_position' => [
            'required' => 'Tooth position is required',
            'in_list' => 'Please select a valid tooth position'
        ],
        'condition_type' => [
            'required' => 'Condition type is required',
            'in_list' => 'Please select a valid condition type'
        ],
        'treatment_status' => [
            'required' => 'Treatment status is required',
            'in_list' => 'Please select a valid treatment status'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setClinicId'];
    protected $afterInsert = [];

    public function getOdontogramByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
            ->where('examination_id', null)
            ->orderBy('tooth_number', 'ASC')
            ->findAll();
    }

    public function getOdontogramByExamination($examinationId)
    {
        return $this->where('examination_id', $examinationId)
            ->orderBy('tooth_number', 'ASC')
            ->findAll();
    }

    public function updateToothCondition($patientId, $toothNumber, $conditionData)
    {
        $examinationId = $conditionData['examination_id'] ?? null;
        $query = $this->where('patient_id', $patientId)
            ->where('tooth_number', $toothNumber);
        if (!empty($examinationId)) {
            $query->where('examination_id', $examinationId);
        } else {
            $query->where('examination_id', null);
        }
        $existing = $query->first();

        // Map the incoming data to match database fields
        $mappedData = [
            'patient_id' => $patientId,
            'examination_id' => $conditionData['examination_id'] ?? null,
            'tooth_number' => $toothNumber,
            'tooth_position' => $this->getToothPosition($toothNumber),
            'condition_type' => $conditionData['condition_type'] ?? 'healthy',
            'condition_description' => $conditionData['condition_description'] ?? null,
            'treatment_notes' => $conditionData['treatment_notes'] ?? null,
            'treatment_date' => $conditionData['treatment_date'] ?? date('Y-m-d'),
            'treatment_status' => $conditionData['treatment_status'] ?? 'completed',
            'created_by' => $conditionData['created_by'] ?? 1
        ];

        if ($existing) {
            return $this->update($existing['id'], $mappedData);
        } else {
            return $this->insert($mappedData);
        }
    }

    public function getToothCondition($patientId, $toothNumber, $examinationId = null)
    {
        $query = $this->where('patient_id', $patientId)
            ->where('tooth_number', $toothNumber);
        if (!empty($examinationId)) {
            $query->where('examination_id', $examinationId);
        } else {
            $query->where('examination_id', null);
        }
        return $query->first();
    }

    private function countByCondition(int $patientId, array $conditionTypes = null, bool $exclude = false, $examinationId = null): int
    {
        $builder = $this->db->table('odontograms')
            ->where('patient_id', $patientId);

        if (!empty($examinationId)) {
            $builder->where('examination_id', $examinationId);
        } else {
            $builder->where('examination_id', null);
        }

        if ($conditionTypes !== null) {
            if ($exclude) {
                $builder->whereNotIn('condition_type', $conditionTypes);
            } else {
                $builder->whereIn('condition_type', $conditionTypes);
            }
        }

        return $builder->countAllResults();
    }

    public function getOdontogramStats($patientId, $examinationId = null)
    {
        $totalTeeth = 32;
        $problematicTeeth = $this->countByCondition($patientId, ['healthy'], true, $examinationId);

        return [
            'total_conditions' => $this->countByCondition($patientId, null, false, $examinationId),
            'total_teeth' => $totalTeeth,
            'healthy_count' => max(0, $totalTeeth - $problematicTeeth),
            'treated_count' => $this->countByCondition($patientId, ['filling', 'crown', 'bridge', 'implant'], false, $examinationId),
            'needs_treatment_count' => $this->countByCondition($patientId, ['cavity', 'extracted'], false, $examinationId),
            'cavities' => $this->countByCondition($patientId, ['cavity'], false, $examinationId),
            'fillings' => $this->countByCondition($patientId, ['filling'], false, $examinationId),
            'crowns' => $this->countByCondition($patientId, ['crown'], false, $examinationId),
            'extracted' => $this->countByCondition($patientId, ['extracted'], false, $examinationId),
            'root_canals' => $this->countByCondition($patientId, ['root_canal'], false, $examinationId)
        ];
    }

    public function getToothPositions()
    {
        return [
            'upper_right' => 'Upper Right (1-8)',
            'upper_left' => 'Upper Left (9-16)',
            'lower_left' => 'Lower Left (17-24)',
            'lower_right' => 'Lower Right (25-32)'
        ];
    }

    private function getToothPosition($toothNumber)
    {
        if ($toothNumber >= 1 && $toothNumber <= 8) {
            return 'upper_right';
        } elseif ($toothNumber >= 9 && $toothNumber <= 16) {
            return 'upper_left';
        } elseif ($toothNumber >= 17 && $toothNumber <= 24) {
            return 'lower_left';
        } elseif ($toothNumber >= 25 && $toothNumber <= 32) {
            return 'lower_right';
        }
        return 'upper_right'; // Default fallback
    }

    public function getConditionTypes()
    {
        return [
            'healthy' => 'Healthy',
            'cavity' => 'Cavity',
            'filling' => 'Filling',
            'crown' => 'Crown',
            'root_canal' => 'Root Canal',
            'extracted' => 'Extracted',
            'implant' => 'Implant',
            'bridge' => 'Bridge',
            'partial_denture' => 'Partial Denture',
            'full_denture' => 'Full Denture',
            'other' => 'Other'
        ];
    }
}
