<?php
declare(strict_types=1);
namespace App\Controllers { class BaseController { protected $request; protected $session; protected $permissionService; } }
namespace {
require __DIR__.'/../../app/Services/NavigationService.php';
require __DIR__.'/../../app/Controllers/Auth.php';
require __DIR__.'/../../app/Controllers/Settings.php';
$base = 'https://clinic.example/';
function env($key) { return $GLOBALS['base']; }
function config($name) { return (object) ['forceGlobalSecureRequests' => str_starts_with($GLOBALS['base'] ?? '', 'https:')]; }
function base_url(string $path=''): string { return rtrim($GLOBALS['base_url_override'] ?? $GLOBALS['base'],'/').'/'.ltrim($path,'/'); }
$nav = new App\Services\NavigationService();
function service($name) { if ($name !== 'navigation') throw new RuntimeException('Unexpected service'); return $GLOBALS['nav']; }
final class SessionFixture { public array $values=['active_clinic_id'=>1,'user_id'=>9]; public function get($key) { return $this->values[$key]??null; } public function set($key,$value) { $this->values[$key]=$value; } }
$session = new SessionFixture();
function session() { return $GLOBALS['session']; }
final class RedirectFixture { public string $url=''; public array $flash=[]; public function to($url):self { $this->url=$url; return $this; } public function back() { throw new RuntimeException('Untrusted Referer fallback used'); } public function with($key,$value):self { $this->flash[$key]=$value; return $this; } }
function redirect() { return new RedirectFixture(); }
function lang($key) { return $key; }
$n=0;
function ck(bool $ok,string $why):void { global $n; if(!$ok)throw new RuntimeException($why);$n++; }
foreach (['https://clinic.example/','https://clinic.example/dental-saas/'] as $base) {
    $prefix=parse_url($base,PHP_URL_PATH);$root=rtrim($base,'/');
    foreach (['patient/1?tab=visits#recent'=>$root.'/patient/1?tab=visits#recent',
        $prefix.'settings?tab=hours'=>$root.'/settings?tab=hours',
        $root.'/auth/login?next=patients'=>$root.'/auth/login?next=patients',
        $root.'/patients?q=%D8%B9%D9%84%D9%8A'=>$root.'/patients?q=%D8%B9%D9%84%D9%8A',
        $root.'/patients?q=First%20Last'=>$root.'/patients?q=First%20Last'] as $input=>$expected) {
        ck($nav->localReturnUrl($input,'auth/login')===$expected,'Valid local URL preserved: '.$input);
    }
    foreach ([null,[],42,true,'','//evil.example/','https://evil.example/','http://clinic.example/',
        'https://clinic.example.evil.example/','https://evil.example@clinic.example/',
        'https://clinic.example:444/','javascript:alert(1)','data:text/html,test','https:/evil.example/',
        '\\evil.example/','https://clinic.example\\@evil.example/',"patient/1\r\nLocation:https://evil.example/",
        'patient/../settings','./patient/1','../outside','%2e%2e/outside','%252e%252e/outside',
        '%252525252e%252525252e/outside','%2f%2fevil.example/','patient/%5c/evil',
        'patient/1?x=%0d%0aLocation:evil',str_repeat('a',4097)] as $input) {
        ck($nav->localReturnUrl($input,'auth/login')===base_url('auth/login'),'Unsafe target rejected: '.json_encode($input));
    }
    if($prefix!=='/')ck($nav->localReturnUrl('/outside','auth/login')===base_url('auth/login'),'Configured subdirectory confinement');
}
$base='https://clinic.example/';
foreach ([
 'settings?tab=hours&lang=ar'=>'settings?tab=hours',
 'patient/1?lang=en&tab=finances&q=a%26b&x=1&x=2#recent'=>'patient/1?tab=finances&q=a%26b&x=1&x=2#recent',
 'patient/1?l%61ng=ar&lang=en&tab=visits'=>'patient/1?tab=visits',
 'patient/1?lang[]=ar&tab=visits'=>'patient/1?tab=visits',
 'patient/1?lang[locale]=ar&tab=visits'=>'patient/1?tab=visits',
 'patients?q=lang%3Dar&tab=all'=>'patients?q=lang%3Dar&tab=all',
 'settings?lang=ar#hours'=>'settings#hours',
 'settings?tab=hours'=>'settings?tab=hours',
] as $input=>$expected) ck($nav->languageReturnUrl($input)===base_url($expected),'Language override removed without changing context: '.$input);
ck($nav->languageReturnUrl('https://evil.example/?lang=ar','settings')===base_url('settings'),'Language redirect still rejects external origins');
$base='http://127.0.0.1:57113/dental-saas/';
ck($nav->localReturnUrl('http://127.0.0.1:57113/dental-saas/patient/1')==='http://127.0.0.1:57113/dental-saas/patient/1','Configured nondefault port');
ck($nav->localReturnUrl('http://127.0.0.1/dental-saas/patient/1','auth/login')===base_url('auth/login'),'Different effective port denied');
$base='https://clinic.example/';
$GLOBALS['base_url_override']='https://evil.example/';ck($nav->localReturnUrl('https://evil.example/','auth/login')==='https://clinic.example/auth/login','Request-derived helper host cannot become redirect authority');unset($GLOBALS['base_url_override']);
$unconfigured=$base;$base=null;ck($nav->localReturnUrl('https://evil.example/','auth/login')==='/auth/login','Missing trusted configuration has only a local fallback');$base=$unconfigured;
foreach ([App\Controllers\Auth::class,App\Controllers\Settings::class] as $class) {
    foreach (['GET','POST'] as $method) foreach ([false,true] as $canEdit) {
        $controller=(new ReflectionClass($class))->newInstanceWithoutConstructor();
        $request=new class($method) { public array $values=['return_to'=>'https://evil.example/']; public function __construct(private string $method) {} public function getGet($key) { return $this->values[$key]??null; } public function getPost($key) { return $this->values[$key]??null; } public function getMethod() { return $this->method; } };
        foreach (['request'=>$request,'session'=>$session,'permissionService'=>new class($canEdit) { public function __construct(private bool $allowed) {} public function hasPermission($user,$module,$action) { if($user!==9||$module!=='settings'||$action!=='edit')throw new RuntimeException('Wrong authority');return $this->allowed; } }] as $name=>$value) (new ReflectionProperty($controller,$name))->setValue($controller,$value);
        $writes=new class { public array $rows=[];public function setSetting(...$args) { $this->rows[]=$args;return true; } };
        if($class===App\Controllers\Settings::class) {
            (new ReflectionProperty($controller,'settingsModel'))->setValue($controller,$writes);
            (new ReflectionProperty($controller,'settingsService'))->setValue($controller,new class { public function reloadSettings() {} });
        }
        $response=$controller->switchLanguage('ar');
        ck($response->url===base_url($class===App\Controllers\Auth::class?'auth/login':'settings'),'Actual language controller external-return containment');
        ck($session->get('ui_locale')==='ar','Personal language retained');
        ck(count($writes->rows)===($class===App\Controllers\Settings::class&&$method==='POST'&&$canEdit?1:0),'GET and unprivileged POST do not change clinic default');
        $request->values['return_to']='patient/1?tab=visits';
        ck($controller->switchLanguage('en')->url===base_url('patient/1?tab=visits'),'Actual internal roundtrip');
        $request->values['return_to']='settings?tab=hours&lang=ar';
        ck($controller->switchLanguage('en')->url===base_url('settings?tab=hours'),'Actual switch cannot be reversed by stale query locale');
        ck($session->get('ui_locale')==='en','Selected language remains authoritative');
        ck($controller->switchLanguage('invalid')->url===base_url($class===App\Controllers\Auth::class?'auth/login':'settings'),'Invalid locale cannot fall back to external Referer');
    }
}
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Actual navigation validator and Auth/Settings language methods with synthetic request/session/settings-write traps; configured origins, subpaths, attacks and permissions. No routed authentication or database claim']),PHP_EOL;
}
