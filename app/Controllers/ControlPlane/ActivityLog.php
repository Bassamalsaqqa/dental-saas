<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ControlPlaneAuditModel;
use App\Models\UserModel;

class ActivityLog extends BaseController
{
    protected $auditModel;
    protected $userModel;

    public function __construct()
    {
        $this->auditModel = new ControlPlaneAuditModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Global Mode check is handled by filter, but we can double check permission if needed.
        
        $data = [
            'title' => 'Global Activity Log',
            'pageTitle' => 'Control Plane Activity',
            // Initial data load
            'activities' => $this->getActivities(50, 0),
            'filters' => [
                'event_types' => $this->getUniqueEventTypes()
            ]
        ];

        return view('control_plane/activity_log', $data);
    }

    public function api()
    {
        $limitRaw = $this->request->getGet('limit');
        $offsetRaw = $this->request->getGet('offset');
        $limit = (is_numeric($limitRaw) ? (int) $limitRaw : 50);
        $offset = (is_numeric($offsetRaw) ? (int) $offsetRaw : 0);
        
        if ($limit <= 0) $limit = 50;
        if ($offset < 0) $offset = 0;

        $eventType = $this->request->getGet('event_type');
        $userId = $this->request->getGet('user');
        if ($userId === null) {
            $userId = $this->request->getGet('user_id');
        }

        $activities = $this->getActivities($limit, $offset, $eventType, $userId);
        $total = $this->getTotalCount($eventType, $userId);

        return $this->response->setJSON([
            'success' => true,
            'activities' => $activities,
            'total' => $total
        ]);
    }

    public function export()
    {
        $eventType = $this->request->getGet('event_type');
        $userFilter = $this->request->getGet('user');
        $userId = $userFilter !== null ? $userFilter : $this->request->getGet('user_id');

        $builder = $this->auditModel
            ->select('control_plane_audits.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.id = control_plane_audits.actor_user_id', 'left')
            ->orderBy('control_plane_audits.created_at', 'DESC');

        if ($eventType) {
            $builder->where('control_plane_audits.event_type', $eventType);
        }
        if ($userId) {
            if (is_numeric($userId)) {
                $builder->where('control_plane_audits.actor_user_id', $userId);
            } else {
                $builder->groupStart()
                    ->like('users.first_name', $userId)
                    ->orLike('users.last_name', $userId)
                    ->orLike('users.email', $userId)
                    ->groupEnd();
            }
        }

        $rows = $builder->get()->getResultArray();
        $filename = 'control_plane_activity_' . date('Ymd_His') . '.csv';

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, [
            'ID',
            'Date',
            'User',
            'Email',
            'Event Type',
            'Method',
            'Route',
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
                $row['event_type'],
                $row['method'] ?? '',
                $row['route'] ?? '',
                $row['ip'] ?? '',
                $row['user_agent'] ?? ''
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

    private function getActivities($limit = 50, $offset = 0, $eventType = null, $userId = null)
    {
        try {
            $builder = $this->auditModel
                ->select('control_plane_audits.*, users.first_name, users.last_name, users.email')
                ->join('users', 'users.id = control_plane_audits.actor_user_id', 'left')
                ->orderBy('control_plane_audits.created_at', 'DESC')
                ->limit($limit, $offset);

            if ($eventType) {
                $builder->where('control_plane_audits.event_type', $eventType);
            }
            if ($userId) {
                // If user filter is a name/email, we might need a subquery or join filter.
                // For simplicity, let's assume it's exact ID match if numeric, or simple LIKE on name if string.
                // But the UI sends what? The UI input is text "Search by user name...".
                // So we should filter by joined columns.
                if (is_numeric($userId)) {
                     $builder->where('control_plane_audits.actor_user_id', $userId);
                } else {
                    $builder->groupStart()
                        ->like('users.first_name', $userId)
                        ->orLike('users.last_name', $userId)
                        ->orLike('users.email', $userId)
                        ->groupEnd();
                }
            }

            $activities = $builder->findAll();

            return array_map([$this, 'formatActivity'], $activities);
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch CP activities: ' . $e->getMessage());
            return [];
        }
    }

    private function getTotalCount($eventType = null, $userId = null)
    {
        try {
            $builder = $this->auditModel
                ->join('users', 'users.id = control_plane_audits.actor_user_id', 'left');

            if ($eventType) {
                $builder->where('control_plane_audits.event_type', $eventType);
            }
            if ($userId) {
                if (is_numeric($userId)) {
                     $builder->where('control_plane_audits.actor_user_id', $userId);
                } else {
                    $builder->groupStart()
                        ->like('users.first_name', $userId)
                        ->orLike('users.last_name', $userId)
                        ->orLike('users.email', $userId)
                        ->groupEnd();
                }
            }

            return $builder->countAllResults();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function formatActivity($activity)
    {
        return [
            'id' => $activity['id'],
            'user_name' => ($activity['first_name'] ?? 'System') . ' ' . ($activity['last_name'] ?? ''),
            'user_email' => $activity['email'] ?? 'system@internal',
            'action' => $activity['method'] ?? 'INFO',
            'entity_type' => $activity['event_type'], // Mapping event_type to entity_type slot
            'entity_id' => null, // No specific entity ID in this table usually
            'description' => $activity['route'], // Route as description
            'title' => ucwords(str_replace('_', ' ', $activity['event_type'])),
            'icon' => $this->getIconForEvent($activity['event_type']),
            'color' => $this->getColorForEvent($activity['event_type']),
            'ip_address' => $activity['ip'],
            'user_agent' => $activity['user_agent'],
            'metadata' => $activity['metadata_json'] ? json_decode($activity['metadata_json'], true) : null,
            'created_at' => $activity['created_at'],
            'formatted_time' => $this->formatTime($activity['created_at'])
        ];
    }

    private function getUniqueEventTypes()
    {
        try {
            $query = $this->auditModel->query("SELECT DISTINCT event_type FROM control_plane_audits ORDER BY event_type ASC");
            $result = $query->getResultArray();
            return array_column($result, 'event_type');
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getIconForEvent($eventType)
    {
        if (strpos($eventType, 'login') !== false) return 'fas fa-sign-in-alt';
        if (strpos($eventType, 'logout') !== false) return 'fas fa-sign-out-alt';
        if (strpos($eventType, 'create') !== false) return 'fas fa-plus-circle';
        if (strpos($eventType, 'update') !== false) return 'fas fa-edit';
        if (strpos($eventType, 'delete') !== false) return 'fas fa-trash';
        if (strpos($eventType, 'view') !== false || strpos($eventType, 'get') !== false) return 'fas fa-eye';
        return 'fas fa-history';
    }

    private function getColorForEvent($eventType)
    {
        if (strpos($eventType, 'delete') !== false) return 'red';
        if (strpos($eventType, 'create') !== false) return 'green';
        if (strpos($eventType, 'update') !== false) return 'blue';
        if (strpos($eventType, 'login') !== false) return 'teal';
        return 'gray';
    }

    private function formatTime($timestamp)
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return date('M j, Y', $time);
    }
}
