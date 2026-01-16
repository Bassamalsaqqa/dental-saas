<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;

class Danger extends BaseController
{
    /**
     * Guard: Ensure Global Mode is active
     */
    private function ensureGlobalMode()
    {
        if (!session()->get('global_mode')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Danger Zone requires Global Mode activation.');
        }
    }

    /**
     * GET /controlplane/danger
     */
    public function index()
    {
        $this->ensureGlobalMode();

        return view('control_plane/danger', [
            'title' => 'Danger Zone',
            'global_mode' => true
        ]);
    }

    /**
     * POST /controlplane/danger/exit
     * High-friction session termination
     */
    public function exitGlobalMode()
    {
        $this->ensureGlobalMode();

        $confirmation = $this->request->getPost('confirm_terminate');
        $phrase = $this->request->getPost('termination_phrase');

        if (!$confirmation) {
            return redirect()->back()->with('error', 'You must acknowledge the session termination policy.');
        }

        if ($phrase !== 'EXIT GLOBAL MODE') {
            return redirect()->back()->with('error', 'Incorrect termination phrase. Access retained.');
        }

        // Execution: Reusing the core exit logic
        session()->set('global_mode', false);
        session()->regenerate();

        return redirect()->to('/clinic/select')->with('success', 'Global Mode exited. Select a clinic to continue.');
    }
}
