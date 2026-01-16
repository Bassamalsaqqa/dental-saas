<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;

class PlanManagement extends BaseController
{
    protected $planModel;

    public function __construct()
    {
        $this->planModel = new PlanModel();
    }

    private function ensureGlobalMode()
    {
        if (!session()->get('global_mode')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access Denied');
        }
    }

    public function index()
    {
        $this->ensureGlobalMode();

        $data = [
            'title' => 'Plan Management',
            'plans' => $this->planModel->findAll()
        ];

        return view('control_plane/plans/index', $data);
    }

    public function create()
    {
        $this->ensureGlobalMode();
        return view('control_plane/plans/form', ['title' => 'Create Plan', 'plan' => null]);
    }

    public function edit($id)
    {
        $this->ensureGlobalMode();
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return redirect()->to('/controlplane/plans')->with('error', 'Plan not found.');
        }

        return view('control_plane/plans/form', ['title' => 'Edit Plan', 'plan' => $plan]);
    }

    public function store()
    {
        $this->ensureGlobalMode();

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'status' => 'required|in_list[active,inactive]',
            'features_json' => 'required', // Should validate JSON format?
            'limits_json' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $features = $this->request->getPost('features_json');
        $limits = $this->request->getPost('limits_json');

        // Validate JSON
        if (!json_decode($features) || !json_decode($limits)) {
             return redirect()->back()->withInput()->with('error', 'Invalid JSON format.');
        }

        // Validity Rule: Block activation unless required limits exist
        $status = $this->request->getPost('status');
        if ($status === 'active') {
            $limitsArray = json_decode($limits, true);
            $requiredLimits = ['patients_active_max', 'exports'];
            foreach ($requiredLimits as $key) {
                if (!array_key_exists($key, $limitsArray)) {
                    return redirect()->back()->withInput()->with('error', "Active plans must define limit: $key");
                }
            }
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'status' => $status,
            'features_json' => $features,
            'limits_json' => $limits,
        ];

        $id = $this->request->getPost('id');
        
        if ($id) {
            $this->planModel->update($id, $data);
            $message = 'Plan updated successfully.';
        } else {
            $this->planModel->insert($data);
            $message = 'Plan created successfully.';
        }

        return redirect()->to('/controlplane/plans')->with('success', $message);
    }

    public function toggleStatus($id)
    {
        $this->ensureGlobalMode();
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Plan not found']);
        }

        $newStatus = ($plan['status'] === 'active') ? 'inactive' : 'active';

        // Check validity before activating
        if ($newStatus === 'active') {
            $limits = json_decode($plan['limits_json'], true);
            $requiredLimits = ['patients_active_max', 'exports'];
            foreach ($requiredLimits as $key) {
                if (!array_key_exists($key, $limits)) {
                    return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => "Cannot activate: Missing limit $key."]);
                }
            }
        }

        $this->planModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON(['success' => true, 'new_status' => $newStatus]);
    }
}
