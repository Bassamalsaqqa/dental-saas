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
            'create' => lang('UI.notificationMessagePatientCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessagePatientUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'delete' => lang('UI.notificationMessagePatientDelete', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageAppointmentCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageAppointmentUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'cancel' => lang('UI.notificationMessageAppointmentCancel', ['name' => lang('UI.notificationUnknownUser')]),
            'reschedule' => lang('UI.notificationMessageAppointmentUpdate', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageExaminationCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageExaminationUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'complete' => lang('UI.notificationMessageExaminationUpdate', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageTreatmentCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageTreatmentUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'complete' => lang('UI.notificationMessageTreatmentComplete', ['name' => lang('UI.notificationUnknownUser')]),
            'cancel' => lang('UI.notificationMessageTreatmentUpdate', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageInventoryCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageInventoryUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'delete' => lang('UI.notificationMessageInventoryUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'low_stock' => lang('UI.notificationMessageInventoryLowStock'),
            'usage' => lang('UI.notificationMessageInventoryUpdate', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageFinanceCreate', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageFinanceUpdate', ['name' => lang('UI.notificationUnknownUser')]),
            'refund' => lang('UI.notificationMessageFinanceUpdate', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'delete' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'expire' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
            'create' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'update' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'delete' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'login' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
            'logout' => lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]),
        ];

        $description = $description ?: ($defaultDescriptions[$action] ?? lang('UI.notificationMessageSystemActivity', ['name' => lang('UI.notificationUnknownUser')]));

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
