<?php
// Standalone focused test: no framework bootstrap, database, network or notification transport.
require __DIR__ . '/../../app/Services/ClinicWorkflowFormatter.php';
require __DIR__ . '/../../app/Services/WorkflowTemplatePayload.php';
require __DIR__ . '/../../app/Services/ClinicWorkflowClaimService.php';
use App\Services\ClinicWorkflowFormatter as Formatter;
use App\Services\WorkflowTemplatePayload as Payload;
use App\Services\ClinicWorkflowClaimService as Claim;
$checks = 0;
function check($condition, string $message): void { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } }
function rejects(callable $call, string $message): void { try { $call(); } catch (DomainException $e) { check(true, $message); return; } check(false, $message); }
$a = new Formatter(['timezone' => 'Asia/Jerusalem', 'date_format' => 'd/m/Y', 'time_format' => '12']);
$b = new Formatter(['timezone' => 'America/New_York', 'date_format' => 'm/d/Y', 'time_format' => '24']);
$utc = new Formatter(['timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => '24']);
$now = new DateTimeImmutable('2026-06-10T01:00:00Z');
check($a->currentDate($now) === '2026-06-10', 'Jerusalem day');
check($b->currentDate($now) === '2026-06-09', 'New York day');
check($a->date('2026-06-10') === '10/06/2026' && $b->date('2026-06-10') === '06/10/2026', 'Calendar dates unchanged across zones');
check($utc->date('2026-06-10') === '2026-06-10', 'ISO calendar format');
$patient = ['id' => 2, 'clinic_id' => 1, 'first_name' => 'مريم Mary', 'last_name' => 'Smith'];
$source = ['id' => 3, 'clinic_id' => 1, 'patient_id' => 2, 'status' => 'scheduled', 'appointment_date' => '2026-06-10', 'appointment_time' => '13:05:00'];
$clinic = ['clinic_name' => 'عيادة Clinic', 'clinic_phone' => '123', 'clinic_address' => 'Street'];
$vars = $a->appointmentVars($patient, $source, $clinic);
check($vars['patient_name'] === 'مريم Mary Smith' && $vars['clinic_name'] === 'عيادة Clinic', 'AR/EN text retained exactly');
check($vars['appointment_time'] === '1:05 PM', '12 hour Latin AM/PM');
check($b->appointmentVars($patient, $source, $clinic)['appointment_time'] === '13:05', '24 hour target clinic');
check($b->examinationVars($patient, ['examination_date' => '2026-06-10'], $clinic)['examination_date'] === '06/10/2026', 'Exam target formatting');
foreach ([['timezone' => 'bad/zone'], ['timezone' => ''], ['date_format' => 'bogus'], ['time_format' => '13']] as $settings) { rejects(fn() => new Formatter($settings), 'Invalid settings rejected'); }
rejects(fn() => $a->date('2026-02-30'), 'Malformed date');
rejects(fn() => $a->instant('2026-06-10', '25:00:00'), 'Malformed time');
rejects(fn() => $b->instant('2026-03-08', '02:30:00'), 'New York DST gap');
rejects(fn() => $b->instant('2026-11-01', '01:30:00'), 'New York DST fold');
rejects(fn() => $a->instant('2026-03-27', '02:30:00'), 'Jerusalem DST gap');
rejects(fn() => $a->instant('2026-10-25', '01:30:00'), 'Jerusalem DST fold');
$dstNow = new DateTimeImmutable('2026-03-08T06:30:00Z');
$dstVisit = ['status' => 'scheduled', 'appointment_date' => '2026-03-08', 'appointment_time' => '03:30:00'];
check($b->reminderEligible($dstVisit, 60, $dstNow), 'DST elapsed hour accepted despite two wall hours');
check(!$b->reminderEligible($dstVisit, 59, $dstNow), 'Exact lead edge');
check(!$b->reminderEligible($dstVisit, 60, new DateTimeImmutable('2026-03-08T07:30:01Z')), 'Past visit excluded');
$dates = $b->discoveryDates(60, $dstNow);
check($dates[0] <= '2026-03-08' && $dates[1] >= '2026-03-08', 'DST discovery superset');
$config = ['name' => 'test_template', 'language' => 'ar', 'header_param_key' => 'clinic_name', 'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'], 'button_param_key' => 'clinic_phone', 'button_type' => 'url', 'button_index' => '0'];
$payload = ['workflow_trigger' => 'appointment_confirmation', 'workflow_key' => 'appointment_confirmation:3:2026-06-10:13:05:00', 'template_source' => 'workflow_trigger', 'template_name' => 'test_template', 'template_language' => 'ar', 'template_vars' => $vars, 'template_components' => Payload::components($config, $vars)];
$note = ['clinic_id' => 1, 'recipient_type' => 'patient', 'recipient_id' => 2];
check(Payload::matches($payload, $config, $vars), 'Canonical Meta payload accepted');
foreach (['template_name', 'template_language', 'template_vars', 'template_components'] as $field) { $bad = $payload; $bad[$field] = 'tampered'; check(!Payload::matches($bad, $config, $vars), 'Tamper rejected: ' . $field); }
foreach ([0, 1, 2] as $index) { $bad = $payload; $bad['template_components'][$index]['parameters'][0][array_key_exists('text', $bad['template_components'][$index]['parameters'][0]) ? 'text' : 'payload'] = 'tampered'; check(!Payload::matches($bad, $config, $vars), 'Actual transmitted header/body/button tamper'); }
check(Claim::sourceMatches($note, $payload, $source, $patient, 1, $a, $now), 'Current source accepted');
foreach (['patient_id' => 4, 'clinic_id' => 2, 'status' => 'cancelled', 'appointment_date' => '2026-06-11', 'appointment_time' => '14:00:00'] as $field => $value) { $bad = $source; $bad[$field] = $value; check(!Claim::sourceMatches($note, $payload, $bad, $patient, 1, $a, $now), 'Stale/foreign source ' . $field); }
$badPatient = $patient; $badPatient['clinic_id'] = 2;
check(!Claim::sourceMatches($note, $payload, $source, $badPatient, 1, $a, $now), 'Cross-clinic patient');
$follow = ['workflow_trigger' => 'follow_up', 'workflow_key' => 'follow_up:3'];
$exam = ['id' => 3, 'clinic_id' => 1, 'patient_id' => 2, 'status' => 'completed'];
check(Claim::sourceMatches($note, $follow, $exam, $patient, 1, $a, $now), 'Completed examination');
$exam['status'] = 'pending'; check(!Claim::sourceMatches($note, $follow, $exam, $patient, 1, $a, $now), 'Reopened examination');
$reminderSource = ['id' => 3, 'clinic_id' => 1, 'patient_id' => 2, 'status' => 'scheduled', 'appointment_date' => '2026-03-08', 'appointment_time' => '03:30:00'];
$reminder = ['workflow_trigger' => 'appointment_reminder', 'workflow_key' => 'appointment_reminder:3:2026-03-08:03:30:00'];
check(Claim::sourceMatches($note, $reminder, $reminderSource, $patient, 1, $b, $dstNow, 60), 'Reminder exact lead source accepted');
check(!Claim::sourceMatches($note, $reminder, $reminderSource, $patient, 1, $b, $dstNow, 59), 'Reminder outside lead source rejected');
$source['status'] = 'no_show';
$noShow = ['workflow_trigger' => 'no_show_follow_up', 'workflow_key' => 'no_show_follow_up:3:2026-06-10'];
check(Claim::sourceMatches($note, $noShow, $source, $patient, 1, $a, $now), 'Clinic-local no-show key');
$priorDay = $noShow; $priorDay['workflow_key'] = 'no_show_follow_up:3:2026-06-09';
check(Claim::sourceMatches($note, $priorDay, $source, $patient, 1, $a, $now), 'Prior-day delayed no-show remains eligible');
$futureDay = $noShow; $futureDay['workflow_key'] = 'no_show_follow_up:3:2026-06-11';
check(!Claim::sourceMatches($note, $futureDay, $source, $patient, 1, $a, $now), 'Future enqueue day rejected');
$invalidDay = $noShow; $invalidDay['workflow_key'] = 'no_show_follow_up:3:2026-02-30';
check(!Claim::sourceMatches($note, $invalidDay, $source, $patient, 1, $a, $now), 'Malformed enqueue day rejected');
$reordered = $payload; $reordered['template_vars'] = array_reverse($vars, true);
check(Payload::matches($reordered, $config, $vars), 'JSON object key order is immaterial');
echo json_encode(['status' => 'PASS', 'checks' => $checks, 'database' => false, 'outbound' => false], JSON_PRETTY_PRINT), PHP_EOL;
