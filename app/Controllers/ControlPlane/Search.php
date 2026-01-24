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
            return $this->response->setJSON(['results' => $results]);
        }

        $clinics = (new ClinicModel())
            ->select('id, name, domain, slug, uuid')
            ->groupStart()
                ->orLike('name', $query, 'both')
                ->orLike('slug', $query, 'both')
                ->orLike('uuid', $query, 'both')
                ->orLike('domain', $query, 'both')
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit(5)
            ->findAll();

        foreach ($clinics as $clinic) {
            $results['clinics'][] = [
                'id' => (int) ($clinic['id'] ?? 0),
                'name' => $clinic['name'] ?? '',
                'domain' => $clinic['domain'] ?? '',
            ];
        }

        $users = (new UserModel())
            ->select('id, email, first_name, last_name')
            ->groupStart()
                ->orLike('email', $query, 'both')
                ->orLike('first_name', $query, 'both')
                ->orLike('last_name', $query, 'both')
            ->groupEnd()
            ->orderBy('email', 'ASC')
            ->limit(5)
            ->findAll();

        foreach ($users as $user) {
            $name = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $results['users'][] = [
                'id' => (int) ($user['id'] ?? 0),
                'email' => $user['email'] ?? '',
                'name' => $name !== '' ? $name : null,
            ];
        }

        $navLinks = [
            ['title' => 'Dashboard', 'url' => 'controlplane/dashboard', 'keywords' => ['overview', 'metrics']],
            ['title' => 'Clinics', 'url' => 'controlplane/clinics', 'keywords' => ['tenants', 'customers', 'care']],
            ['title' => 'Plans', 'url' => 'controlplane/plans', 'keywords' => ['catalog', 'pricing', 'offers']],
            ['title' => 'Plan Requests', 'url' => 'controlplane/plan-requests', 'keywords' => ['queue', 'approve', 'change']],
            ['title' => 'Operations', 'url' => 'controlplane/operations', 'keywords' => ['audit', 'activity', 'logs']],
            ['title' => 'Settings', 'url' => 'controlplane/settings', 'keywords' => ['configuration', 'policy']],
        ];

        $needleLower = mb_strtolower($query);
        foreach ($navLinks as $link) {
            $haystack = array_merge([$link['title']], $link['keywords'] ?? []);
            foreach ($haystack as $term) {
                if (mb_stripos(mb_strtolower((string) $term), $needleLower) !== false) {
                    $results['links'][] = [
                        'title' => $link['title'],
                        'url' => $link['url'],
                    ];
                    break;
                }
            }
            if (count($results['links']) >= 5) {
                break;
            }
        }

        return $this->response->setJSON(['results' => $results]);
    }
}
