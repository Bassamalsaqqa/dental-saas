<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\UserModel;

class Search extends BaseController
{
    /**
     * GET /controlplane/search/global
     */
    public function global()
    {
        $query = trim((string) $this->request->getGet('q'));
        $results = [
            'clinics' => [],
            'users' => [],
            'links' => [],
        ];

        if (mb_strlen($query) < 2) {
            return $this->response->setJSON([
                'query' => $query,
                'results' => $results,
            ]);
        }

        $clinicModel = new ClinicModel();
        $clinicQuery = $clinicModel->select('id, name, slug, status')
            ->groupStart()
            ->like('name', $query)
            ->orLike('slug', $query)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit(6);

        if (ctype_digit($query)) {
            $clinicQuery = $clinicModel->select('id, name, slug, status')
                ->groupStart()
                ->where('id', (int) $query)
                ->orLike('name', $query)
                ->orLike('slug', $query)
                ->groupEnd()
                ->orderBy('name', 'ASC')
                ->limit(6);
        }

        $results['clinics'] = $clinicQuery->findAll();

        $userModel = new UserModel();
        $users = $userModel->select('id, email, first_name, last_name')
            ->like('email', $query)
            ->orderBy('email', 'ASC')
            ->limit(6)
            ->findAll();

        $results['users'] = array_map(static function (array $user) {
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            return [
                'id' => $user['id'],
                'email' => $user['email'] ?? '',
                'name' => $name !== '' ? $name : null,
            ];
        }, $users);

        $navLinks = [
            ['title' => 'Dashboard', 'url' => 'controlplane/dashboard', 'keywords' => ['overview', 'metrics']],
            ['title' => 'Clinics', 'url' => 'controlplane/clinics', 'keywords' => ['tenants', 'customers']],
            ['title' => 'Onboarding', 'url' => 'controlplane/onboarding/clinic/create', 'keywords' => ['create clinic', 'new clinic']],
            ['title' => 'Plans', 'url' => 'controlplane/plans', 'keywords' => ['catalog', 'pricing']],
            ['title' => 'Plan Requests', 'url' => 'controlplane/plan-requests', 'keywords' => ['approvals', 'queue']],
            ['title' => 'Operations', 'url' => 'controlplane/operations', 'keywords' => ['audit', 'activity']],
            ['title' => 'Settings', 'url' => 'controlplane/settings', 'keywords' => ['configuration']],
            ['title' => 'Email Templates', 'url' => 'controlplane/settings#template-editor', 'keywords' => ['templates', 'emails']],
        ];

        $needle = mb_strtolower($query);
        foreach ($navLinks as $link) {
            $haystack = array_merge([$link['title']], $link['keywords'] ?? []);
            $match = false;
            foreach ($haystack as $term) {
                if (mb_strpos(mb_strtolower((string) $term), $needle) !== false) {
                    $match = true;
                    break;
                }
            }
            if ($match) {
                $results['links'][] = [
                    'title' => $link['title'],
                    'url' => $link['url'],
                ];
            }
        }

        return $this->response->setJSON([
            'query' => $query,
            'results' => $results,
        ]);
    }
}
