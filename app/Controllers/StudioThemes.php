<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Services\PermissionService;
use App\Services\StudioThemeCatalog;
use App\Services\StudioThemeStore;
use App\Services\StudioThemeAccess;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Native appearance editor. Scope is fixed by authenticated route/session, never form/query IDs. */
class StudioThemes extends BaseController
{
    public function tenant() { return $this->editor(false); }
    public function platform() { return $this->editor(true); }
    public function saveTenant() { return $this->save(false); }
    public function savePlatform() { return $this->save(true); }

    private function context(bool $platform): array
    {
        if (!config('StudioThemes')->enabled || !$this->ionAuth->loggedIn()) throw PageNotFoundException::forPageNotFound();
        $actor = (int)$this->ionAuth->getUserId();
        $clinic = $platform ? null : (int)session('active_clinic_id');
        if (($platform && session('global_mode') !== true) || (!$platform && (session('global_mode') === true || $clinic <= 0))) throw PageNotFoundException::forPageNotFound();
        $db = \Config\Database::connect();
        $store = new StudioThemeStore($db, true);
        if (!$store->available() || !(new StudioThemeAccess())->allows($db,$clinic,$actor,false)) throw PageNotFoundException::forPageNotFound();
        return [$db,$store,$clinic,$actor];
    }

    private function editor(bool $platform)
    {
        [$db,$store,$clinic] = $this->context($platform);
        $this->response->setHeader('Cache-Control','private, no-store');
        $presets = [];
        foreach (StudioThemeCatalog::presets() as $id => $_) $presets[$id] = StudioThemeCatalog::normalize(['preset'=>$id]);
        return view('settings/appearance',[
            'title'=>lang('Themes.title'),'platformEditor'=>$platform,
            'themeState'=>$store->load($clinic),'themeHistory'=>$store->history($clinic),
            'themePresets'=>$presets,'canPublish'=>session('is_impersonating') !== true && ($platform || (new PermissionService($db))->hasPermission((int)$this->ionAuth->getUserId(),'settings','edit')),
            'saveUrl'=>base_url($platform?'controlplane/settings/appearance':'settings/appearance'),
        ]);
    }

    private function save(bool $platform)
    {
        [$db,$store,$clinic,$actor] = $this->context($platform);
        $this->response->setHeader('Cache-Control','private, no-store');
        $intent = $this->request->getPost('intent');
        if (!in_array($intent,['preview','publish','inherit','revert'],true)) return $this->response->setStatusCode(422)->setJSON(['error'=>lang('Themes.invalid')]);
        try {
            $input = ['schema'=>1,'preset'=>$this->request->getPost('preset'),'colors'=>$this->request->getPost('colors'),'density'=>$this->request->getPost('density'),'text_size'=>$this->request->getPost('text_size')];
            if ($intent === 'preview') {
                $theme = StudioThemeCatalog::normalize($input);
                return $this->response->setJSON(['theme'=>$theme,'css'=>StudioThemeCatalog::css($theme),'csrf'=>['name'=>csrf_token(),'hash'=>csrf_hash()]]);
            }
            $version = $this->request->getPost('version');
            $platformVersion = $this->request->getPost('platform_version');
            if (!is_string($version) || !ctype_digit($version) || strlen($version)>10 || !is_string($platformVersion) || !ctype_digit($platformVersion) || strlen($platformVersion)>10) throw new \DomainException('Invalid revision.');
            $reason = $this->request->getPost('reason');
            if (!is_string($reason)) throw new \DomainException('Missing reason.');
            $revert = null;
            if ($intent === 'revert') {
                $raw = $this->request->getPost('revert_version');
                if (!is_string($raw) || !ctype_digit($raw) || strlen($raw)>10) throw new \DomainException('Invalid recovery revision.');
                $revert=(int)$raw;
            }
            $store->publish($clinic,$actor,(int)$version,(int)$platformVersion,$intent==='publish'?$input:null,$reason,
                fn($connection,$scope,$user)=>(new StudioThemeAccess())->allows($connection,$scope,$user,true),$revert);
            return redirect()->to(base_url($platform?'controlplane/settings/appearance':'settings/appearance'))->with('success',lang('Themes.saved'));
        } catch (\DomainException $e) {
            // No raw SQL or source details in browser errors. Input remains in the native form on validation failure.
            if ($intent === 'preview') return $this->response->setStatusCode(422)->setJSON(['error'=>lang('Themes.invalid'),'csrf'=>['name'=>csrf_token(),'hash'=>csrf_hash()]]);
            return redirect()->to(base_url($platform?'controlplane/settings/appearance':'settings/appearance'))->withInput()->with('error',lang('Themes.saveFailed'));
        } catch (\Throwable $e) {
            log_message('error','Theme operation failed: {type}',['type'=>get_class($e)]);
            return $this->response->setStatusCode(500)->setBody(esc(lang('Themes.unavailable')));
        }
    }
}
