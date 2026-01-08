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

        $data = [
            'title' => 'Activity Log',
            'pageTitle' => 'Activity Log',
            'activities' => $this->getActivities(),
            'filters' => $this->getFilters()
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

        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $entityType = $this->request->getGet('entity_type');
        $action = $this->request->getGet('action');
        $userId = $this->request->getGet('user_id');

        $activities = $this->getActivities($limit, $offset, $entityType, $action, $userId);

        return $this->response->setJSON([
            'success' => true,
            'activities' => $activities,
            'total' => $this->getTotalCount($entityType, $action, $userId)
        ]);
    }

    private function getActivities($limit = 50, $offset = 0, $entityType = null, $action = null, $userId = null)
    {
        try {
            $builder = $this->activityLogModel->db->table('activity_logs al')
                ->select('al.*, u.first_name, u.last_name, u.email')
                ->join('users u', 'u.id = al.user_id', 'left')
                ->orderBy('al.created_at', 'DESC')
                ->limit($limit, $offset);

            // Apply filters
            if ($entityType) {
                $builder->where('al.entity_type', $entityType);
            }
            if ($action) {
                $builder->where('al.action', $action);
            }
            if ($userId) {
                $builder->where('al.user_id', $userId);
            }

            $activities = $builder->get()->getResultArray();

            // Format activities for display
            return array_map([$this, 'formatActivity'], $activities);
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch activities: ' . $e->getMessage());
            return [];
        }
    }

    private function getTotalCount($entityType = null, $action = null, $userId = null)
    {
        try {
            $builder = $this->activityLogModel->db->table('activity_logs al');

            // Apply filters
            if ($entityType) {
                $builder->where('al.entity_type', $entityType);
            }
            if ($action) {
                $builder->where('al.action', $action);
            }
            if ($userId) {
                $builder->where('al.user_id', $userId);
            }

            return $builder->countAllResults();
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

    private function getFilters()
    {
        try {
            $entityTypes = $this->activityLogModel->db->table('activity_logs')
                ->select('entity_type')
                ->distinct()
                ->get()
                ->getResultArray();

            $actions = $this->activityLogModel->db->table('activity_logs')
                ->select('action')
                ->distinct()
                ->get()
                ->getResultArray();

            return [
                'entity_types' => array_column($entityTypes, 'entity_type'),
                'actions' => array_column($actions, 'action')
            ];
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch filters: ' . $e->getMessage());
            return [
                'entity_types' => [],
                'actions' => []
            ];
        }
    }
}
