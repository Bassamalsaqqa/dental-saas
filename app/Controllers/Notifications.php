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
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Notifications',
            'notifications' => $this->getNotifications(),
        ];

        return $this->view('notifications/index', $data);
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

        $notifications = $this->getNotifications();
        $unreadCount = count(array_filter($notifications, function($notification) {
            return !$notification['is_read'];
        }));

        // Debug logging
        log_message('debug', "Total notifications: " . count($notifications));
        log_message('debug', "Unread count: " . $unreadCount);
        log_message('debug', "Notifications data: " . json_encode($notifications));

        return $this->response->setJSON([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id = null)
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        if ($id) {
            // Mark specific notification as read
            $this->markNotificationAsRead($id);
        } else {
            // Mark all notifications as read
            $this->markAllNotificationsAsRead();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notification(s) marked as read'
        ]);
    }

    public function delete($id)
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        // In a real application, you would delete from database
        // For now, just return success
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    private function getNotifications()
    {
        try {
            // Get recent activities from the activity log
            $activities = $this->activityLogModel->getRecentActivities(20);
            
            // If no activities found, return empty array (no sample notifications)
            // This prevents showing fake notifications on fresh installations
            if (empty($activities)) {
                return [];
            }
            
            return $activities;
        } catch (\Exception $e) {
            // Log the error and return empty array as fallback
            log_message('error', 'Failed to fetch activity logs: ' . $e->getMessage());
            return [];
        }
    }

    private function markNotificationAsRead($id)
    {
        // In a real application, you would update the database
        // For now, store in session
        $readNotifications = session()->get('read_notifications') ?? [];
        if (!in_array($id, $readNotifications)) {
            $readNotifications[] = $id;
            session()->set('read_notifications', $readNotifications);
        }
        return true;
    }

    private function markAllNotificationsAsRead()
    {
        // Set the current timestamp as the last read time
        $currentTime = time();
        session()->set('last_notification_read_time', $currentTime);
        
        log_message('debug', "Marked all notifications as read at: " . date('Y-m-d H:i:s', $currentTime));
        
        return true;
    }
}