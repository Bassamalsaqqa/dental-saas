<?php

declare(strict_types=1);
namespace Tests\Unit;

use Tests\Support\PhaseBFixture;

/** Actual view rendering; fixtures contain synthetic data only and need no remote assets. */
final class ScheduleImpactUiRenderingTest extends PhaseBFixture
{
    private const AFFECTED = 505; // 1,010 traditional action/reason inputs exceed max_input_vars=1000.

    public function testActualPreviewHasOneReasonPerVisitAndExplicitAcknowledgmentInBothLocales(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $data = $this->viewData($locale);
            $html = view('settings/schedule_impact_preview', $data);
            [$doc, $xpath] = $this->parse($html);
            $fields = $xpath->query('//form[@id="schedule-impact-apply"]//textarea[starts-with(@name,"dispositions[")]');
            $ids = [];
            foreach ($fields as $field) {
                if (preg_match('/^dispositions\[([0-9]+)\]\[reason\]$/D', $field->getAttribute('name'), $match) !== 1) {
                    $this->fail('Malformed disposition reason identity.');
                }
                $ids[] = (int) $match[1];
            }
            $this->assertSame($data['report']['affected_appointment_ids'], $ids);
            $this->assertSame(count($ids), count(array_unique($ids)), 'A visit must not have duplicate desktop/mobile controls.');
            $ack = $xpath->query('//form[@id="schedule-impact-apply"]//input[@name="acknowledged"]');
            $this->assertSame(1, $ack->length);
            $this->assertSame('checkbox', $ack->item(0)->getAttribute('type'));
            $this->assertTrue($ack->item(0)->hasAttribute('required'));
            $this->assertFalse($ack->item(0)->hasAttribute('checked'), 'Acknowledgment must be an explicit operator action.');
            $this->assertLiteralEvidence($xpath, $data);
            $refresh = $xpath->query('//form[@id="schedule-impact-apply"]//button[@name="refresh_preview"]');
            $this->assertSame(1, $refresh->length);
            $this->assertTrue($refresh->item(0)->hasAttribute('formnovalidate'));
            $this->writeBrowserFixture($doc, $data, 'preview', $locale);
        }
    }

    public function testActualRecheckShowsEscapedRetainedEvidenceAndExplicitReviewAction(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $data = $this->viewData($locale);
            $data['report']['items'] = [$data['report']['items'][0], end($data['report']['items'])];
            $data['report']['affected_appointment_ids'] = [700001];
            $data['report']['total_future_visits'] = 2;
            $data['report']['after_conflicts_count'] = 1;
            $html = view('settings/schedule_impact_report', $data);
            [$doc, $xpath] = $this->parse($html);
            $this->assertSame(0, $xpath->query('//textarea[starts-with(@name,"dispositions[")]')->length);
            $this->assertSame(0, $xpath->query('//svg|//img[@src="impact-hostile"]|//script[contains(text(),"impactBad()")]')->length);
            $this->assertStringContainsString($data['retained_exceptions'][700001]['reason'], $xpath->query('//main')->item(0)->textContent);
            $this->assertStringContainsString('#'.$this->providers[0], $xpath->query('//main')->item(0)->textContent);
            $review = $xpath->query('//main//form[.//input[@name="refresh_preview"]]');
            $this->assertSame(1, $review->length);
            $this->assertSame('post', strtolower($review->item(0)->getAttribute('method')));
            $this->assertSame(1, $xpath->query('//main//input[@name="proposed_dto_json"]')->length);
            $this->writeBrowserFixture($doc, $data, 'recheck', $locale);
        }
    }

    private function viewData(string $locale): array
    {
        $this->login($locale);
        helper(['form', 'url', 'settings', 'auth', 'navigation', 'html']);
        service('request')->setLocale($locale);
        $clinicRow = $this->db->table('clinics')->where('id', $this->clinic)->get()->getRowArray();
        $clinic = array_replace(settings()->getClinicInfo(), $clinicRow);
        $clinic['tagline'] = $clinic['tagline'] ?? 'Synthetic schedule review';
        $clinic['logo_path'] = ''; // Keep fixture rendering independent of stored or remote image assets.
        $user = $this->db->table('users')->where('id', $this->actor)->get()->getRowArray();
        $arabic = "\u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064a}\u{0629}";
        $hostile = 'Doctor </div><svg onload="impactBad()"> \" '. $arabic;
        $before = 'Before <img src="impact-hostile" onerror="impactBad()"> '. $arabic;
        $after = 'After </textarea><script>impactBad()</script> '. $arabic;
        $items = [];
        for ($i = 0; $i <= self::AFFECTED; $i++) {
            $affected = $i < self::AFFECTED;
            $items[] = ['appointment_id'=>700001+$i, 'clinic_id'=>$this->clinic, 'patient_id'=>$this->patient,
                'appointment_date'=>'2030-01-07', 'appointment_time'=>'10:00', 'duration'=>30, 'end_time'=>'10:30',
                'status'=>$i === 0 ? 'confirmed' : 'scheduled', 'provider_user_id'=>$this->providers[$i % 4],
                'provider_name'=>$i === 0 ? $hostile : 'Duplicate Doctor', 'chair_name'=>'Preserved historical snapshot',
                'before_outcome'=>['compliant'=>$i !== 0, 'code'=>$i === 0 ? 'PREEXISTING' : 'COMPLIANT', 'reason'=>$i === 0 ? $before : 'Before compliant'],
                'after_outcome'=>['compliant'=>!$affected, 'code'=>$affected ? 'OUTSIDE_PROVIDER_HOURS' : 'COMPLIANT', 'reason'=>$i === 0 ? $after : 'After synthetic interval'],
                'is_affected'=>$affected, 'is_new_conflict'=>$affected && $i !== 0, 'is_resolved'=>false,
                'reschedulable'=>true, 'edit_url'=>$i === 0 ? base_url('appointment/700001/edit') : null];
        }
        $dto = ['working_hours_start'=>'11:00','working_hours_end'=>'17:00','working_days'=>['monday'],
            'appointment_duration'=>'30','dynamic_doctor_scheduling_enabled'=>'1'];
        return ['title'=>'Synthetic impact regression', 'clinic'=>$clinic, 'current_clinic'=>$clinic,
            'user'=>(object)$user, 'user_clinics'=>[$clinic], 'active_clinic_id'=>$this->clinic,
            'is_rtl'=>$locale === 'ar', 'target_label'=>$hostile, 'target_type'=>'clinic_hours', 'target_id'=>null,
            'proof_token'=>str_repeat('a',64), 'proposed_dto_json'=>json_encode($dto,JSON_THROW_ON_ERROR), 'current_dto'=>$dto,
            'latest_audit'=>['applied_at'=>'2030-01-01 12:00:00','target_type'=>'clinic_hours','affected_count'=>1,
                'state_digest'=>str_repeat('b',64),'actor_user_id'=>$this->actor],
            'retained_exceptions'=>[700001=>['reason'=>'Retain <svg onload="impactBad()"> '. $arabic,'actor_id'=>$this->actor]],
            'report'=>['clinic_id'=>$this->clinic,'target_type'=>'clinic_hours','target_id'=>null,
                'total_future_visits'=>count($items),'before_conflicts_count'=>1,'after_conflicts_count'=>self::AFFECTED,
                'newly_conflicted_count'=>self::AFFECTED-1,'resolved_conflicts_count'=>0,
                'affected_appointment_ids'=>range(700001,700000+self::AFFECTED),'state_digest'=>str_repeat('b',64),'items'=>$items]];
    }

    private function parse(string $html): array
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        return [$doc, new \DOMXPath($doc)];
    }

    private function assertLiteralEvidence(\DOMXPath $xpath, array $data): void
    {
        $this->assertSame(0, $xpath->query('//svg|//img[@src="impact-hostile"]|//script[contains(text(),"impactBad()")]')->length);
        $text = $xpath->query('//main')->item(0)->textContent;
        foreach (['provider_name', 'before_outcome', 'after_outcome'] as $key) {
            $value = $data['report']['items'][0][$key];
            $this->assertStringContainsString(is_array($value) ? $value['reason'] : $value, $text);
        }
        $this->assertStringContainsString('#'.$this->providers[0], $text);
        $this->assertStringNotContainsString('DoctorScheduleSettings.', $text);
    }

    private function writeBrowserFixture(\DOMDocument $doc, array $data, string $surface, string $locale): void
    {
        $xpath = new \DOMXPath($doc);
        $main = $xpath->query('//main')->item(0);
        $this->assertNotNull($main);
        $content = '';
        foreach ($main->childNodes as $node) $content .= $doc->saveHTML($node);
        $metadata = ['surface'=>$surface,'locale'=>$locale,'affected_ids'=>$data['report']['affected_appointment_ids'],
            'provider_id'=>$this->providers[0],'hostile_provider'=>$data['report']['items'][0]['provider_name'],
            'before_reason'=>$data['report']['items'][0]['before_outcome']['reason'],
            'after_reason'=>$data['report']['items'][0]['after_outcome']['reason'],
            'retain_reason'=>$data['retained_exceptions'][700001]['reason']];
        $json = json_encode($metadata,JSON_THROW_ON_ERROR|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $css = 'file:///'.str_replace('\\','/',ROOTPATH).'public/assets/css/tailwind.css';
        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        $html = '<!doctype html><html lang="'.$locale.'" dir="'.$dir.'"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="'.$css.'"><style>body{margin:0;font-family:Arial,sans-serif}</style></head><body>'.$content.'<script id="impact-fixture-data" type="application/json">'.$json.'</script></body></html>';
        $name = $surface === 'preview' ? 'mobile' : 'recheck';
        file_put_contents(ROOTPATH.'tmp/schedule-impact-'.$name.'-'.$locale.'.html',$html);
    }
}
