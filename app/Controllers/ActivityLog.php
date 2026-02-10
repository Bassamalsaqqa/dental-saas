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

        $limitRaw = $this->request->getGet('limit');
        $offsetRaw = $this->request->getGet('offset');
        $limit = (is_numeric($limitRaw) ? (int) $limitRaw : 50);
        $offset = (is_numeric($offsetRaw) ? (int) $offsetRaw : 0);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($offset < 0) {
            $offset = 0;
        }
        $entityType = $this->request->getGet('entity_type');
        $action = $this->request->getGet('action');
        $userFilter = $this->request->getGet('user');
        if ($userFilter === null) {
            $userFilter = $this->request->getGet('user_id');
        }
        [$userId, $userQuery] = $this->parseUserFilter($userFilter);

        $activities = $this->getActivities($limit, $offset, $entityType, $action, $userId, $userQuery, $clinicId);

        return $this->response->setJSON([
            'success' => true,
            'activities' => $activities,
            'total_all' => $this->getTotalCount(null, null, null, null, $clinicId),
            'total_filtered' => $this->getTotalCount($entityType, $action, $userId, $userQuery, $clinicId)
        ]);
    }

    public function export()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        if (!has_permission('activity_log', 'view')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to export activity logs.');
        }

        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to export activity logs.');
        }

        $entityType = $this->request->getGet('entity_type');
        $action = $this->request->getGet('action');
        $userFilter = $this->request->getGet('user');
        if ($userFilter === null) {
            $userFilter = $this->request->getGet('user_id');
        }

        [$userId, $userQuery] = $this->parseUserFilter($userFilter);

        $builder = $this->activityLogModel->builder();
        $builder->select('activity_logs.*, users.first_name, users.last_name, users.email');
        $builder->join('users', 'users.id = activity_logs.user_id', 'left');
        $builder->where('activity_logs.clinic_id', $clinicId);
        $builder->orderBy('activity_logs.created_at', 'DESC');

        if ($entityType) {
            $builder->where('activity_logs.entity_type', $entityType);
        }
        if ($action) {
            $builder->where('activity_logs.action', $action);
        }
        if ($userId) {
            $builder->where('activity_logs.user_id', $userId);
        } elseif ($userQuery) {
            $builder->groupStart()
                ->like('users.first_name', $userQuery)
                ->orLike('users.last_name', $userQuery)
                ->orLike('users.email', $userQuery)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        $filename = 'activity_log_' . date('Ymd_His') . '.csv';

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, [
            'ID',
            'Date',
            'User',
            'Email',
            'Action',
            'Entity Type',
            'Entity ID',
            'Description',
            'IP Address',
            'User Agent'
        ]);

        foreach ($rows as $row) {
            $userName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            fputcsv($handle, [
                $row['id'],
                $row['created_at'],
                $userName !== '' ? $userName : 'System',
                $row['email'] ?? '',
                $row['action'],
                $row['entity_type'],
                $row['entity_id'],
                $row['description'],
                $row['ip_address'],
                $row['user_agent']
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    private function getActivities($limit = 50, $offset = 0, $entityType = null, $action = null, $userId = null, $userQuery = null, $clinicId = null)
    {
        try {
            if (!$clinicId) {
                return [];
            }

            $limit = (int) $limit;
            $offset = (int) $offset;
            if ($limit <= 0) {
                $limit = 50;
            }
            if ($offset < 0) {
                $offset = 0;
            }

            $builder = $this->activityLogModel->forClinic($clinicId)
                ->select('activity_logs.*, users.first_name, users.last_name, users.email')
                ->join('users', 'users.id = activity_logs.user_id', 'left')
                ->orderBy('activity_logs.created_at', 'DESC')
                ->limit($limit, $offset);

            if ($entityType) {
                $builder->where('activity_logs.entity_type', $entityType);
            }
            if ($action) {
                $builder->where('activity_logs.action', $action);
            }
            if ($userId) {
                $builder->where('activity_logs.user_id', $userId);
            } elseif ($userQuery) {
                $builder->groupStart()
                    ->like('users.first_name', $userQuery)
                    ->orLike('users.last_name', $userQuery)
                    ->orLike('users.email', $userQuery)
                    ->groupEnd();
            }

            $activities = $builder->findAll();

            // Format activities for display
            return array_map([$this, 'formatActivity'], $activities);
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch activities: ' . $e->getMessage());
            return [];
        }
    }

    private function getTotalCount($entityType = null, $action = null, $userId = null, $userQuery = null, $clinicId = null)
    {
        try {
            if (!$clinicId) {
                return 0;
            }

            $builder = $this->activityLogModel->builder();
            $builder->select('activity_logs.id');
            $builder->join('users', 'users.id = activity_logs.user_id', 'left');
            $builder->where('activity_logs.clinic_id', $clinicId);

            if ($entityType) {
                $builder->where('activity_logs.entity_type', $entityType);
            }
            if ($action) {
                $builder->where('activity_logs.action', $action);
            }
            if ($userId) {
                $builder->where('activity_logs.user_id', $userId);
            } elseif ($userQuery) {
                $builder->groupStart()
                    ->like('users.first_name', $userQuery)
                    ->orLike('users.last_name', $userQuery)
                    ->orLike('users.email', $userQuery)
                    ->groupEnd();
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
                'update' => ['title' => 'Payment Updated', 'icon' => 'fas fa-edit', 'color' => 'blue'],
                'refund' => ['title' => 'Payment Refunded', 'icon' => 'fas fa-undo', 'color' => 'red']
            ],
            'prescription' => [
                'create' => ['title' => 'Prescription Created', 'icon' => 'fas fa-prescription-bottle-alt', 'color' => 'indigo'],
                'update' => ['title' => 'Prescription Updated', 'icon' => 'fas fa-edit', 'color' => 'blue'],
                'delete' => ['title' => 'Prescription Deleted', 'icon' => 'fas fa-trash', 'color' => 'red']
            ],
            'user' => [
                'create' => ['title' => 'User Created', 'icon' => 'fas fa-user-plus', 'color' => 'green'],
                'update' => ['title' => 'User Updated', 'icon' => 'fas fa-user-edit', 'color' => 'blue'],
                'delete' => ['title' => 'User Deleted', 'icon' => 'fas fa-user-times', 'color' => 'red']
            ],
            'auth' => [
                'password_reset_failed' => ['title' => 'Password Reset Blocked', 'icon' => 'fas fa-unlock-alt', 'color' => 'red']
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

    private function parseUserFilter($userFilter): array
    {
        $userFilter = is_string($userFilter) ? trim($userFilter) : '';
        if ($userFilter === '') {
            return [null, null];
        }

        if (ctype_digit($userFilter)) {
            return [(int) $userFilter, null];
        }

        return [null, $userFilter];
    }
}
