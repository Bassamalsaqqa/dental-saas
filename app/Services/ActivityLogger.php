<?php

namespace App\Services;

use App\Models\ActivityLogModel;

class ActivityLogger
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    /**
     * Log patient activities
     */
    public function logPatientActivity($action, $patientId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New patient registered",
            'update' => "Patient information updated",
            'delete' => "Patient record deleted"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Patient {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'patient',
            $patientId,
            $description
        );
    }

    /**
     * Log appointment activities
     */
    public function logAppointmentActivity($action, $appointmentId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New appointment scheduled",
            'update' => "Appointment updated",
            'cancel' => "Appointment cancelled",
            'reschedule' => "Appointment rescheduled"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Appointment {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'appointment',
            $appointmentId,
            $description
        );
    }

    /**
     * Log examination activities
     */
    public function logExaminationActivity($action, $examinationId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New examination conducted",
            'update' => "Examination updated",
            'complete' => "Examination completed"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Examination {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'examination',
            $examinationId,
            $description
        );
    }

    /**
     * Log treatment activities
     */
    public function logTreatmentActivity($action, $treatmentId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New treatment started",
            'update' => "Treatment updated",
            'complete' => "Treatment completed",
            'cancel' => "Treatment cancelled"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Treatment {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'treatment',
            $treatmentId,
            $description
        );
    }

    /**
     * Log inventory activities
     */
    public function logInventoryActivity($action, $inventoryId = null, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New inventory item added",
            'update' => "Inventory item updated",
            'delete' => "Inventory item deleted",
            'low_stock' => "Low stock alert triggered",
            'usage' => "Inventory items used in treatment"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Inventory {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'inventory',
            $inventoryId,
            $description
        );
    }

    /**
     * Log finance activities
     */
    public function logFinanceActivity($action, $financeId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New payment received",
            'update' => "Payment updated",
            'refund' => "Payment refunded"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Finance {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'finance',
            $financeId,
            $description
        );
    }

    /**
     * Log prescription activities
     */
    public function logPrescriptionActivity($action, $prescriptionId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New prescription created",
            'update' => "Prescription updated",
            'delete' => "Prescription deleted",
            'expire' => "Prescription expired"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "Prescription {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'prescription',
            $prescriptionId,
            $description
        );
    }

    /**
     * Log user activities
     */
    public function logUserActivity($action, $targetUserId, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $defaultDescriptions = [
            'create' => "New user created",
            'update' => "User information updated",
            'delete' => "User account deleted",
            'login' => "User logged in",
            'logout' => "User logged out"
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? "User {$action}");

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            'user',
            $targetUserId,
            $description
        );
    }

    /**
     * Log custom activity
     */
    public function logCustomActivity($action, $entityType, $entityId = null, $description = '')
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;

        return $this->activityLogModel->logActivity(
            $userId,
            $action,
            $entityType,
            $entityId,
            $description
        );
    }

    /**
     * Get recent activities for a specific entity
     */
    public function getEntityActivities($entityType, $entityId, $limit = 20)
    {
        $clinicId = session()->get('active_clinic_id');
        return $this->activityLogModel->getEntityActivities($entityType, $entityId, $limit, $clinicId);
    }

    /**
     * Get user activities
     */
    public function getUserActivities($userId, $limit = 50)
    {
        $clinicId = session()->get('active_clinic_id');
        return $this->activityLogModel->getUserActivities($userId, $limit, $clinicId);
    }
}
