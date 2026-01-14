<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class ActivityLog extends BaseController
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    public function index()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        // Check permission
        if (!has_permission('activity_log', 'view')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to view activity logs.');
        }

        // S4-02b: Fail closed if clinic context is missing
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view activity logs.');
        }

        // Get initial data for the view
        // Note: index() loads the view which then calls api() via AJAX usually,
        // but it passes 'activities' and 'filters' initially.
        // We need to scope these too.
        
        $data = [
            'title' => 'Activity Log',
            'pageTitle' => 'Activity Log',
            'activities' => $this->getActivities(50, 0, null, null, null, $clinicId),
            'filters' => $this->getFilters($clinicId)
        ];

        return $this->view('activity_log/index', $data);
    }

    public function api()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        // Check permission
        if (!has_permission('activity_log', 'view')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Permission denied'
            ]);
        }

        // S4-02b: Fail closed if clinic context is missing
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $entityType = $this->request->getGet('entity_type');
        $action = $this->request->getGet('action');
        $userId = $this->request->getGet('user_id');

        $activities = $this->getActivities($limit, $offset, $entityType, $action, $userId, $clinicId);

        return $this->response->setJSON([
            'success' => true,
            'activities' => $activities,
            'total' => $this->getTotalCount($entityType, $action, $userId, $clinicId)
        ]);
    }

    private function getActivities($limit = 50, $offset = 0, $entityType = null, $action = null, $userId = null, $clinicId = null)
    {
        try {
            if (!$clinicId) {
                return [];
            }

            $activities = $this->activityLogModel->getActivitiesByClinic($clinicId, $limit, $offset, $entityType, $action, $userId);

            // Format activities for display
            return array_map([$this, 'formatActivity'], $activities);
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch activities: ' . $e->getMessage());
            return [];
        }
    }

    private function getTotalCount($entityType = null, $action = null, $userId = null, $clinicId = null)
    {
        try {
            if (!$clinicId) {
                return 0;
            }

            return $this->activityLogModel->countActivitiesByClinic($clinicId, $entityType, $action, $userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to count activities: ' . $e->getMessage());
            return 0;
        }
    }

    private function formatActivity($activity)
    {
        $actionConfig = $this->getActionConfig($activity['action'], $activity['entity_type']);
        
        return [
            'id' => $activity['id'],
            'user_name' => $activity['first_name'] . ' ' . $activity['last_name'],
            'user_email' => $activity['email'],
            'action' => $activity['action'],
            'entity_type' => $activity['entity_type'],
            'entity_id' => $activity['entity_id'],
            'description' => $activity['description'] ?: $this->buildDescription($activity),
            'title' => $actionConfig['title'],
            'icon' => $actionConfig['icon'],
            'color' => $actionConfig['color'],
            'ip_address' => $activity['ip_address'],
            'user_agent' => $activity['user_agent'],
            'metadata' => $activity['metadata'] ? json_decode($activity['metadata'], true) : null,
            'created_at' => $activity['created_at'],
            'formatted_time' => $this->formatTime($activity['created_at'])
        ];
    }

    private function getActionConfig($action, $entityType)
    {
        $configs = [
            'patient' => [
                'create' => ['title' => 'Patient Registered', 'icon' => 'fas fa-user-plus', 'color' => 'green'],
                'update' => ['title' => 'Patient Updated', 'icon' => 'fas fa-user-edit', 'color' => 'blue'],
                'delete' => ['title' => 'Patient Deleted', 'icon' => 'fas fa-user-times', 'color' => 'red']
            ],
            'appointment' => [
                'create' => ['title' => 'Appointment Scheduled', 'icon' => 'fas fa-calendar-plus', 'color' => 'blue'],
                'update' => ['title' => 'Appointment Updated', 'icon' => 'fas fa-calendar-check', 'color' => 'purple'],
                'cancel' => ['title' => 'Appointment Cancelled', 'icon' => 'fas fa-calendar-times', 'color' => 'red']
            ],
            'examination' => [
                'create' => ['title' => 'Examination Conducted', 'icon' => 'fas fa-stethoscope', 'color' => 'green'],
                'update' => ['title' => 'Examination Updated', 'icon' => 'fas fa-clipboard-check', 'color' => 'blue']
            ],
            'treatment' => [
                'create' => ['title' => 'Treatment Started', 'icon' => 'fas fa-tooth', 'color' => 'green'],
                'update' => ['title' => 'Treatment Updated', 'icon' => 'fas fa-edit', 'color' => 'blue'],
                'complete' => ['title' => 'Treatment Completed', 'icon' => 'fas fa-check-circle', 'color' => 'green']
            ],
            'inventory' => [
                'create' => ['title' => 'Inventory Added', 'icon' => 'fas fa-box', 'color' => 'green'],
                'update' => ['title' => 'Inventory Updated', 'icon' => 'fas fa-edit', 'color' => 'blue'],
                'low_stock' => ['title' => 'Low Stock Alert', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'yellow']
            ],
            'finance' => [
                'create' => ['title' => 'Payment Received', 'icon' => 'fas fa-dollar-sign', 'color' => 'green'],
                'update' => ['title' => 'Payment Updated', 'icon' => 'fas fa-edit', 'color' => 'blue']
            ]
        ];

        return $configs[$entityType][$action] ?? [
            'title' => ucfirst($action) . ' ' . ucfirst($entityType),
            'icon' => 'fas fa-cog',
            'color' => 'gray'
        ];
    }

    private function buildDescription($activity)
    {
        $userName = $activity['first_name'] . ' ' . $activity['last_name'];
        $entityType = ucfirst($activity['entity_type']);
        $action = ucfirst($activity['action']);
        
        return "{$action} {$entityType} by {$userName}";
    }

    private function formatTime($timestamp)
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }

    private function getFilters($clinicId = null)
    {
        try {
            if (!$clinicId) {
                return [
                    'entity_types' => [],
                    'actions' => []
                ];
            }

            return $this->activityLogModel->getFiltersByClinic($clinicId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch filters: ' . $e->getMessage());
            return [
                'entity_types' => [],
                'actions' => []
            ];
        }
    }

    public function getEntityActivities($entityType, $entityId, $limit = 20)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        return $this->activityLogModel->getEntityActivities($entityType, $entityId, $limit, $clinicId);
    }

    public function getUserActivities($userId, $limit = 50)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        return $this->activityLogModel->getUserActivities($userId, $limit, $clinicId);
    }
}
