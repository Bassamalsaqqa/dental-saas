<?php
// Focused real-view rendering with synthetic values and framework helpers only; no database bootstrap.
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$locale = 'en';
function lang(string $key, array $args = []): string {
    global $root, $locale; [$file,$item] = explode('.', $key, 2);
    static $catalog = [];
    $catalog[$locale][$file] ??= require $root.'/app/Language/'.$locale.'/'.$file.'.php';
    $value = $catalog[$locale][$file][$item] ?? $key;
    foreach ($args as $name=>$text) $value = str_replace('{'.$name.'}', (string)$text, $value);
    return $value;
}
function esc($value, string $context = 'html'): string { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function old(string $key, $default = null) { return $default; }
function base_url(string $path): string { return 'https://synthetic.invalid/'.$path; }
function csrf_field(): string { return '<input type="hidden" name="csrf_test" value="synthetic">'; }
function has_permission(...$args): bool { return true; }
function view(string $file, array $data = []): string { return (new ScheduleUiRenderer())->render($file, $data); }
final class ScheduleUiRenderer {
    public function extend(string $layout): string { return ''; }
    public function section(string $name): string { return ''; }
    public function endSection(): string { return ''; }
    public function render(string $file, array $data): string { global $root; extract($data); ob_start(); include $root.'/app/Views/'.$file.'.php'; return ob_get_clean(); }
}
$checks = 0;
function check(bool $condition, string $message): void { global $checks; if (!$condition) throw new RuntimeException($message); ++$checks; }
function dom(string $html): DOMXPath { $doc = new DOMDocument(); @$doc->loadHTML('<?xml encoding="UTF-8">'.$html); return new DOMXPath($doc); }
$en = require $root.'/app/Language/en/DoctorScheduleSettings.php';
$ar = require $root.'/app/Language/ar/DoctorScheduleSettings.php';
check(array_keys($en) === array_keys($ar), 'Scheduling translation key parity');
foreach ($ar as $key=>$value) {
    check(preg_match('//u',$value) === 1 && !preg_match('/\?{2,}|\x{FFFD}|[ØÙ]/u',$value), 'Valid Arabic: '.$key);
}
foreach (['en','ar'] as $locale) {
    $context = ['settings'=>['working_hours_start'=>'09:00','working_hours_end'=>'17:00','working_days'=>['monday'],'booking_chair_count'=>1,'booking_chair_names'=>['<b>Doctor</b>'],'dynamic_doctor_scheduling_enabled'=>'0'], 'provider_settings'=>['schema_ready'=>false,'members'=>[]], 'is_rtl'=>$locale==='ar'];
    $hours = view('settings/partials/hours',$context);
    $xp = dom($hours);
    check($xp->query('//form[contains(@action,"preview-schedule-impact") and translate(@method,"POST","post")="post"]')->length === 1, 'Hours preview POST');
    check($xp->query('//input[@name="dynamic_doctor_scheduling_enabled" and @type="checkbox" and @disabled]')->length === 1, 'Unavailable dynamic scheduling remains disabled');
    check($xp->query('//input[@name="csrf_test"]')->length === 1, 'Hours CSRF retained');
    check(!str_contains($hours,'<b>Doctor</b>'), 'Chair names escaped');
    check(!preg_match('/\?{2,}/',$hours), 'Hours no broken translation');
    $outcome=['compliant'=>false,'code'=>'OUTSIDE_CLINIC_HOURS','reason'=>'<img src=x onerror=alert(1)>'];
    $item=['appointment_id'=>7,'appointment_date'=>'2030-01-07','appointment_time'=>'10:00','end_time'=>'10:30','duration'=>30,'status'=>'scheduled','provider_user_id'=>null,'chair_name'=>'<script>bad()</script>','before_outcome'=>$outcome,'after_outcome'=>$outcome,'edit_url'=>null];
    $dto=['working_hours_start'=>'11:00','working_hours_end'=>'17:00','working_days'=>['monday'],'appointment_duration'=>'45','dynamic_doctor_scheduling_enabled'=>'0','booking_chair_count'=>'1','booking_chair_names'=>['<b>Proposed</b>']];
    $report=['total_future_visits'=>1,'before_conflicts_count'=>1,'after_conflicts_count'=>1,'state_digest'=>str_repeat('b',64),'items'=>[$item],'affected_appointment_ids'=>[7]];
    $data=['is_rtl'=>$locale==='ar','target_label'=>'Clinic','target_type'=>'clinic_hours','target_id'=>null,'proof_token'=>str_repeat('a',64),'proposed_dto_json'=>json_encode($dto),'report'=>$report,'latest_audit'=>null,'retained_exceptions'=>[],'current_dto'=>$dto];
    $preview=view('settings/schedule_impact_preview',$data); $xp=dom($preview);
    check($xp->query('//form[@id="schedule-impact-apply" and contains(@action,"apply-schedule-impact")]')->length===1,'Apply endpoint preserved');
    check($xp->query('//input[@name="proof_token" and @value="'.str_repeat('a',64).'"]')->length===1,'Proof preserved');
    check($xp->query('//input[@name="acknowledged" and @required and not(@checked)]')->length===1,'Explicit acknowledgment');
    check($xp->query('//textarea[@name="dispositions[7][reason]" and @required and @minlength="3"]')->length===1,'One required retention reason');
    check($xp->query('//button[@name="refresh_preview" and @formnovalidate and contains(@formaction,"preview-schedule-impact")]')->length===1,'Safe refresh preserved');
    check($xp->query('//script[contains(.,"bad()")]|//img')->length===0,'Impact content escaped');
    check(str_contains($preview,'11:00') && str_contains($preview,'45') && str_contains($preview,'&lt;b&gt;Proposed&lt;/b&gt;'),'Proposed schedule visible and escaped');
    check(!str_contains($preview,'OUTSIDE_CLINIC_HOURS') && !preg_match('/\?{2,}/',$preview),'Readable localized preview');
    $recheck=view('settings/schedule_impact_report',$data); $xp=dom($recheck);
    check($xp->query('//form[contains(@action,"apply-schedule-impact")]')->length===0,'Current report never applies changes');
    check($xp->query('//form[contains(@action,"preview-schedule-impact")]')->length===1,'Current report can start explicit review');
    check(!str_contains($recheck,'OUTSIDE_CLINIC_HOURS') && !preg_match('/\?{2,}/',$recheck),'Readable current report');
    $data['target_type']='provider_profile'; $data['target_id']=12;
    $data['target_label']='<script>provider()</script> #12';
    $data['proposed_dto_json']=json_encode(['is_bookable'=>true,'is_clinician'=>false,'weekly_hours'=>['monday'=>[['start'=>'09:15','end'=>'12:45']],'tuesday'=>[]],'exceptions'=>['2030-01-08'=>[], '2030-01-09'=>[['start'=>'10:00','end'=>'11:00']]]]);
    $provider=view('settings/schedule_impact_preview',$data); $xp=dom($provider);
    check(str_contains($provider,'09:15') && str_contains($provider,'12:45') && str_contains($provider,'2030-01-09'),'Provider proposed hours and exceptions visible');
    check($xp->query('//input[@name="target_id" and @value="12"]')->length===1,'Provider identity retained');
    check(!str_contains($provider,'<script>provider()') && str_contains($provider,'&lt;script&gt;provider()'),'Provider target escaped');
    $data['proposed_dto_json']=json_encode(['is_bookable'=>true,'is_clinician'=>false,'weekly_hours'=>null,'exceptions'=>null]);
    $provider=view('settings/schedule_impact_preview',$data);
    check(str_contains($provider,esc(lang('DoctorScheduleSettings.clinicHoursApply'))),'Provider inherited hours explained');
    $context['provider_settings']=['schema_ready'=>true,'members'=>[['user_id'=>12,'name'=>'<b>Same name</b>','role_slug'=>'doctor','profile'=>[]]]];
    $providerForm=view('settings/partials/hours',$context); $xp=dom($providerForm);
    check($xp->query('//form[contains(@action,"preview-schedule-impact")]')->length===2,'Clinic and doctor preview forms separate');
    check($xp->query('//input[@name="provider_user_id" and @value="12"]')->length===1,'Stable doctor identity');
    check(!str_contains($providerForm,'<b>Same name</b>'),'Doctor names escaped');
    $data['target_type']='clinic_hours';$data['target_id']=null;
    $dto['working_days']=[];$data['proposed_dto_json']=json_encode($dto);
    $data['report']['items']=[];$data['report']['after_conflicts_count']=0;
    $empty=view('settings/schedule_impact_preview',$data);
    check(str_contains($empty,esc(lang('DoctorScheduleSettings.noWorkingDays'))),'Closed clinic proposal explained');
    check(str_contains($empty,esc(lang('DoctorScheduleSettings.pendingPreview'))),'No-conflict preview remains pending');

}
echo "PASS: $checks focused translation/rendering safeguards; no database connection.\n";