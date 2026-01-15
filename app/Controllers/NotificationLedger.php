<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Services\NotificationService;

class NotificationLedger extends BaseController
{
    protected $notificationModel;
    protected $notificationService;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/dashboard');
        }

        $status = $this->request->getGet('status');
        $channel = $this->request->getGet('channel');
        
        $query = $this->notificationModel->where('clinic_id', $clinicId);
        
        if ($status) {
            $query->where('status', $status);
        }
        if ($channel) {
            $query->where('channel_type', $channel);
        }

        $data = [
            'title' => 'Notification Ledger',
            'notifications' => $query->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->notificationModel->pager,
            'filters' => ['status' => $status, 'channel' => $channel]
        ];

        return $this->view('notifications/ledger', $data);
    }

    public function retry($id)
    {
        $clinicId = session()->get('active_clinic_id');
        $isGlobal = session()->get('global_mode');
        if (!$clinicId && !$isGlobal) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'No context']);
        }

        try {
            $userId = session()->get('user_id');
            $original = $this->notificationModel->find($id);
            if (!$original) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Notification not found']);
            }

            // Superadmin in global_mode can retry any clinic. Tenant admins only their clinic.
            if ($isGlobal) {
                $clinicId = $original['clinic_id'];
            }

            $this->notificationService->retryNotification($id, $userId, $clinicId);

            return $this->response->setJSON(['success' => true, 'message' => 'Retry queued in ledger.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
