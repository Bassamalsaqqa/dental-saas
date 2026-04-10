<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class Notifications extends BaseController
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $clinicId = (int) session()->get('active_clinic_id');

        if ($userId <= 0) {
            return redirect()->to('/auth/login');
        }

        $notifications = $this->getNotifications();
        $data = [
            'title' => lang('UI.notifications'),
            'notifications' => $notifications,
            'unread_count' => count(array_filter($notifications, static fn (array $notification): bool => empty($notification['is_read']))),
            'active_clinic_id' => $clinicId,
        ];

        return $this->view('notifications/index', $data);
    }

    public function api()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('UI.notificationAuthRequired'),
                'csrf_token' => csrf_hash(),
            ])->setStatusCode(401);
        }

        $notifications = $this->getNotifications();
        $unreadCount = count(array_filter($notifications, static fn (array $notification): bool => empty($notification['is_read'])));

        return $this->response->setJSON([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications,
            'csrf_token' => csrf_hash(),
        ]);
    }

    public function markAsRead($id = null)
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('UI.notificationAuthRequired'),
                'csrf_token' => csrf_hash(),
            ])->setStatusCode(401);
        }

        if ($id) {
            $this->markNotificationAsRead($id);
        } else {
            $this->markAllNotificationsAsRead();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => lang('UI.notificationMarkedRead'),
            'csrf_token' => csrf_hash(),
        ]);
    }

    public function delete($id)
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('UI.notificationAuthRequired'),
                'csrf_token' => csrf_hash(),
            ])->setStatusCode(401);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => lang('UI.notificationDeleteUnsupported'),
            'csrf_token' => csrf_hash(),
        ])->setStatusCode(405);
    }

    private function getNotifications()
    {
        try {
            $clinicId = (int) session()->get('active_clinic_id');
            $userId = (int) session()->get('user_id');
            if ($clinicId <= 0 || $userId <= 0) {
                return [];
            }

            $activities = $this->activityLogModel->getRecentActivities(20, null, $clinicId);
            if (empty($activities)) {
                return [];
            }
            
            return $activities;
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch activity logs: ' . $e->getMessage());
            return [];
        }
    }

    private function markNotificationAsRead($id)
    {
        $notificationId = (int) $id;
        if ($notificationId <= 0) {
            return false;
        }

        $readNotifications = session()->get('read_notifications') ?? [];
        if (!in_array($notificationId, $readNotifications, true)) {
            $readNotifications[] = $notificationId;
            session()->set('read_notifications', $readNotifications);
        }

        return true;
    }

    private function markAllNotificationsAsRead()
    {
        session()->set('last_notification_read_time', time());
        session()->remove('read_notifications');
        return true;
    }
}
