<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\AppointmentSchedulingService;
use App\Services\ScheduleImpactService;
use Tests\Support\PhaseBFixture;

final class ScheduleImpactServiceTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while ($this->db->transDepth>0) $this->db->transRollback();
        $this->db->table('control_plane_audits')->whereIn('actor_user_id',$this->users)->delete();
        $this->db->table('examinations')->whereIn('clinic_id',[$this->clinic,$this->otherClinic])->delete();
        parent::tearDown();
    }

    private function book(array $extra = []): int
    {
        $result = (new AppointmentSchedulingService($this->db))->createForClinic($this->clinic, $this->payload($extra), $this->actor);
        $this->assertTrue($result['success'], json_encode($result));
        return (int) $result['id'];
    }

    public function testReportFullHorizonPreservesAllFutureVisitsAndReleasesCancelled(): void
    {
        $service = new ScheduleImpactService($this->db);

        // 1. Scheduled today
        $today = (new \DateTimeImmutable('now', (new \App\Services\ClinicSchedulingSettings($this->db))->timezone($this->clinic)))->format('Y-m-d');
        $id1 = $this->book(['appointment_date' => $today, 'appointment_time' => '10:00']);

        // 2. Confirmed future visit
        $id2 = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '11:00']);
        $this->db->table('appointments')->where('id', $id2)->update(['status' => 'confirmed']);

        // 3. Completed future visit (remains preserved and reported)
        $id3 = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '12:00']);
        $this->db->table('appointments')->where('id', $id3)->update(['status' => 'completed']);

        // 4. Cancelled visit (releases capacity, omitted from conflict analysis)
        $id4 = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '14:00']);
        $this->db->table('appointments')->where('id', $id4)->update(['status' => 'cancelled']);

        // 5. No_show visit (releases capacity, omitted)
        $id5 = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '15:00']);
        $this->db->table('appointments')->where('id', $id5)->update(['status' => 'no_show']);

        // 6. Unresolved historical NULL provider visit
        $id6 = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '16:00']);
        $this->db->table('appointments')->where('id', $id6)->update(['provider_user_id' => null]);

        $dummyDto = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];

        $report = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $dummyDto);

        // Cancelled and no_show are excluded: exactly 4 visits remain in future horizon ($id1, $id2, $id3, $id6)
        $this->assertSame(4, $report['total_future_visits']);
        $reportedIds = array_column($report['items'], 'appointment_id');
        $this->assertContains($id1, $reportedIds);
        $this->assertContains($id2, $reportedIds);
        $this->assertContains($id3, $reportedIds);
        $this->assertContains($id6, $reportedIds);
        $this->assertNotContains($id4, $reportedIds);
        $this->assertNotContains($id5, $reportedIds);

        // NULL provider item must be reported with UNRESOLVED_PROVIDER
        $nullItem = current(array_filter($report['items'], static fn($i) => $i['appointment_id'] === $id6));
        $this->assertFalse($nullItem['after_outcome']['compliant']);
        $this->assertSame('UNRESOLVED_PROVIDER', $nullItem['after_outcome']['code']);
    }

    public function testBeforeAndAfterConflictEvaluationsForHoursAndProfiles(): void
    {
        $service = new ScheduleImpactService($this->db);

        // Visit at 16:30 for 30m (ends 17:00) on a Monday (2026-11-09)
        $id = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '16:30', 'duration' => 30]);

        // A. Proposed hours end at 16:00 -> OUTSIDE_CLINIC_HOURS
        $hoursDto = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '16:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];
        $reportHours = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $hoursDto);
        $itemHours = current(array_filter($reportHours['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertTrue($itemHours['before_outcome']['compliant']);
        $this->assertFalse($itemHours['after_outcome']['compliant']);
        $this->assertSame('OUTSIDE_CLINIC_HOURS', $itemHours['after_outcome']['code']);

        // B. Proposed days remove Monday -> CLINIC_NON_WORKING_DAY
        $daysDto = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => '30',
            'working_days' => ['tuesday', 'wednesday', 'thursday', 'friday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];
        $reportDays = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $daysDto);
        $itemDays = current(array_filter($reportDays['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($itemDays['after_outcome']['compliant']);
        $this->assertSame('CLINIC_NON_WORKING_DAY', $itemDays['after_outcome']['code']);

        // C. Provider profile: unbookable -> PROVIDER_NOT_BOOKABLE
        $profileDto = [
            'is_bookable' => false,
            'is_clinician' => true,
            'weekly_hours' => null,
            'exceptions' => null,
        ];
        $reportProfile = $service->generateReport($this->db, $this->clinic, 'provider_profile', $profileDto, $this->providers[0]);
        $itemProfile = current(array_filter($reportProfile['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($itemProfile['after_outcome']['compliant']);
        $this->assertSame('PROVIDER_NOT_BOOKABLE', $itemProfile['after_outcome']['code']);

        // D. Provider profile: leave exception on 2026-11-09 -> PROVIDER_ON_LEAVE
        $leaveDto = [
            'is_bookable' => true,
            'is_clinician' => true,
            'weekly_hours' => null,
            'exceptions' => ['2026-11-09' => []],
        ];
        $reportLeave = $service->generateReport($this->db, $this->clinic, 'provider_profile', $leaveDto, $this->providers[0]);
        $itemLeave = current(array_filter($reportLeave['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($itemLeave['after_outcome']['compliant']);
        $this->assertSame('PROVIDER_ON_LEAVE', $itemLeave['after_outcome']['code']);

        // E. Provider profile: lunch break 16:00 - 17:00 -> OUTSIDE_PROVIDER_HOURS
        $breakDto = [
            'is_bookable' => true,
            'is_clinician' => true,
            'weekly_hours' => [
                'monday' => [['start' => '09:00', 'end' => '16:00']],
            ],
            'exceptions' => null,
        ];
        $reportBreak = $service->generateReport($this->db, $this->clinic, 'provider_profile', $breakDto, $this->providers[0]);
        $itemBreak = current(array_filter($reportBreak['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($itemBreak['after_outcome']['compliant']);
        $this->assertSame('OUTSIDE_PROVIDER_HOURS', $itemBreak['after_outcome']['code']);
    }

    public function testCustomRoleWithoutClinicianQualificationFailsEligibility(): void
    {
        $service = new ScheduleImpactService($this->db);

        // Only the synthetic role changes; no shared seed role or other clinic is modified.
        $this->db->table('roles')->where('id', $this->role)->update(['is_doctor'=>0, 'is_medical'=>1]);
        $assistantUser = $this->newUser('Assistant');
        $this->membership($assistantUser, $this->clinic, $this->role);
        // Historical appointment can reference a provider that subsequently loses qualification.
        $id = $this->book(['appointment_time'=>'10:00']);
        $this->db->table('appointments')->where('id', $id)->update(['provider_user_id'=>$assistantUser]);

        // Without is_clinician override: PROVIDER_NOT_ELIGIBLE
        $dtoWithoutClinician = [
            'is_bookable' => true,
            'is_clinician' => false,
            'weekly_hours' => null,
            'exceptions' => null,
        ];
        $report = $service->generateReport($this->db, $this->clinic, 'provider_profile', $dtoWithoutClinician, $assistantUser);
        $item = current(array_filter($report['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($item['after_outcome']['compliant']);
        $this->assertSame('PROVIDER_NOT_ELIGIBLE', $item['after_outcome']['code']);

        // With explicit is_clinician override: COMPLIANT
        $dtoWithClinician = [
            'is_bookable' => true,
            'is_clinician' => true,
            'weekly_hours' => null,
            'exceptions' => null,
        ];
        $reportQualified = $service->generateReport($this->db, $this->clinic, 'provider_profile', $dtoWithClinician, $assistantUser);
        $itemQualified = current(array_filter($reportQualified['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertTrue($itemQualified['after_outcome']['compliant']);
        $this->assertSame('COMPLIANT', $itemQualified['after_outcome']['code']);
    }

    public function testStateDigestSensitivityToContentAndLinks(): void
    {
        $service = new ScheduleImpactService($this->db);

        $id = $this->book(['appointment_time' => '10:00']);
        $digest1 = $service->computeStateDigest($this->db, $this->clinic);
        $this->assertNotEmpty($digest1);

        // Same state yields identical digest
        $this->assertSame($digest1, $service->computeStateDigest($this->db, $this->clinic));

        // Add second appointment -> digest must change
        $id2 = $this->book(['appointment_time' => '11:00']);
        $digest2 = $service->computeStateDigest($this->db, $this->clinic);
        $this->assertNotSame($digest1, $digest2);

        // Update appointment time -> digest must change
        $this->db->table('appointments')->where('id', $id)->update(['appointment_time' => '10:30:00']);
        $digest3 = $service->computeStateDigest($this->db, $this->clinic);
        $this->assertNotSame($digest2, $digest3);

        // Add clinical link (examination) -> digest must change
        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'appointment_id' => $id,
            'patient_id' => $this->patient,
            'examination_date' => '2026-11-09',
            'examination_id' => 'EX'.bin2hex(random_bytes(7)),
            'examination_type' => 'initial', 'chief_complaint' => 'Synthetic digest link',
        ]);
        $digest4 = $service->computeStateDigest($this->db, $this->clinic);
        $this->assertNotSame($digest3, $digest4);
    }

    public function testProofIssuanceExpirationAndValidation(): void
    {
        $service = new ScheduleImpactService($this->db);

        $id = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '16:30', 'duration' => 30]);
        $proposedHours = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '16:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];

        $report = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $proposedHours);
        $this->assertSame(1, $report['after_conflicts_count']);

        $token = $service->issueProof($this->clinic, $this->actor, 'clinic_hours', $proposedHours, $report);
        $this->assertSame(64, strlen($token));

        // Valid dispositions
        $validDispositions = [
            $id => ['action' => 'retain', 'reason' => 'Approved out-of-hours exception by director'],
        ];

        // Valid validation
        $error = $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            $validDispositions,
            $report
        );
        $this->assertNull($error);

        // Missing acknowledgment
        $this->assertSame('unacknowledgedImpact', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            false,
            $validDispositions,
            $report
        ));

        // Missing disposition
        $this->assertSame('missingDisposition', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            [],
            $report
        ));

        // Short reason
        $this->assertSame('reasonRequired', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            [$id => ['action' => 'retain', 'reason' => 'ok']],
            $report
        ));

        // Foreign appointment ID in dispositions
        $this->assertSame('foreignDisposition', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            [
                $id => ['action' => 'retain', 'reason' => 'Valid reason'],
                999999 => ['action' => 'retain', 'reason' => 'Foreign visit'],
            ],
            $report
        ));

        // Expired proof
        $sessionProofs = session('schedule_impact_proofs');
        $key = $this->clinic . ':clinic_hours:0';
        $sessionProofs[$key]['expires_at'] = time() - 10;
        session()->set('schedule_impact_proofs', $sessionProofs);

        $this->assertSame('staleProof', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            $validDispositions,
            $report
        ));
    }

    public function testSecurityAndIsolationBoundaries(): void
    {
        $service = new ScheduleImpactService($this->db);
        $id = $this->book();

        $proposedHours = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];
        $report = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $proposedHours);
        $token = $service->issueProof($this->clinic, $this->actor, 'clinic_hours', $proposedHours, $report);

        $dispositions = [$id => ['action' => 'retain', 'reason' => 'Director exception']];

        // 1. Cross-clinic rejection
        $this->assertSame('forbidden', $service->validateProofAndDispositions(
            $this->db,
            $this->otherClinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            $dispositions,
            $report
        ));

        // 2. Different actor rejection
        $otherActor = $this->newUser('OtherOp');
        $this->membership($otherActor, $this->clinic, $this->role);
        $this->assertSame('forbidden', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $otherActor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            $dispositions,
            $report
        ));

        // 3. Bearer blending rejection
        ApiRuntimeContext::setUserId($this->actor);
        ApiRuntimeContext::setClinicId($this->clinic);
        $this->assertSame('forbidden', $service->validateProofAndDispositions(
            $this->db,
            $this->clinic,
            $this->actor,
            'clinic_hours',
            $proposedHours,
            null,
            $token,
            true,
            $dispositions,
            $report
        ));
        ApiRuntimeContext::clear();
    }

    public function testDurableAuditEvidencePersistenceAndClinicScopedRead(): void
    {
        $service = new ScheduleImpactService($this->db);
        $id = $this->book();
        $dto = $service->validateProposedHoursDto(['working_hours_start'=>'09:00','working_hours_end'=>'09:30',
            'appointment_duration'=>'30','working_days'=>['monday','tuesday'],'dynamic_doctor_scheduling_enabled'=>'1'], $this->clinic);
        $report = $service->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
        $this->assertSame([$id],$report['affected_appointment_ids']);
        $dispositions = [$id=>['action'=>'retain','reason'=>'Retained by operator']];
        $this->assertTrue($this->db->transBegin());
        try {
            $service->recordAuditEvidence($this->db,$this->clinic,$this->actor,'clinic_hours',null,
                ['working_hours_start'=>'09:00','working_hours_end'=>'17:00'],$dto,$report,$dispositions);
            $this->assertTrue($this->db->transCommit());
        } finally { if($this->db->transDepth>0)$this->db->transRollback(); }
        $record = $service->getLatestAuditRecord($this->clinic);
        $this->assertNotNull($record);
        $this->assertSame($this->clinic,$record['clinic_id']);
        $this->assertSame($this->actor,$record['actor_user_id']);
        $this->assertSame('clinic_hours',$record['target_type']);
        $this->assertSame($dispositions,$record['dispositions']);
        $this->assertNull($service->getLatestAuditRecord($this->otherClinic));
        $row = $this->db->table('control_plane_audits')->where('event_type','schedule_impact_bundle_part')
            ->where('actor_user_id',$this->actor)->get()->getRowArray();
        $this->assertNotNull($row);
        $meta=json_decode($row['metadata_json'],true,512,JSON_THROW_ON_ERROR);
        $this->assertSame($this->clinic,$meta['clinic_id']);
        $this->assertSame(0,$meta['part_index']);
    }

    public function testCorruptProviderProfileFailsClosed(): void
    {
        $service = new ScheduleImpactService($this->db);
        $id = $this->book(['appointment_date' => '2026-11-09', 'appointment_time' => '10:00']);

        // Insert corrupt weekly_hours_json in clinic_provider_profiles
        $this->db->table('clinic_provider_profiles')->insert([
            'clinic_id' => $this->clinic,
            'user_id' => $this->providers[0],
            'is_bookable' => 1,
            'is_clinician' => 1,
            'weekly_hours_json' => '{malformed_json: true,',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $dto = [
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ];

        $report = $service->generateReport($this->db, $this->clinic, 'clinic_hours', $dto);
        $item = current(array_filter($report['items'], static fn($i) => $i['appointment_id'] === $id));
        $this->assertFalse($item['after_outcome']['compliant']);
        $this->assertSame('PROVIDER_PROFILE_CORRUPT', $item['after_outcome']['code']);
    }

    public function testValidateProposedHoursDtoRejectsMalformedAndPreservesCurrentFlag(): void
    {
        $service = new ScheduleImpactService($this->db);

        // Unknown key rejection
        try {
            $service->validateProposedHoursDto(['unknown_key' => 'val']);
            $this->fail('Unknown key must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('invalidInput', $e->getMessage());
        }

        // Invalid time range (start >= end)
        try {
            $service->validateProposedHoursDto([
                'working_hours_start' => '17:00',
                'working_hours_end' => '09:00',
                'appointment_duration' => 30,
                'working_days' => ['monday'],
            ]);
            $this->fail('Invalid time range must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('invalidInput', $e->getMessage());
        }

        // Missing dynamic flag preserves current clinic setting
        $this->db->table('settings')->where(['clinic_id' => $this->clinic, 'setting_key' => 'dynamic_doctor_scheduling_enabled'])->update(['setting_value' => '1']);
        $dto = $service->validateProposedHoursDto([
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => 30,
            'working_days' => ['monday', 'tuesday'],
        ], $this->clinic);
        $this->assertSame('1', $dto['dynamic_doctor_scheduling_enabled']);
        $this->assertArrayNotHasKey('booking_chair_count', $dto);
    }

    public function testValidateProposedProfileDtoRejectsUnknownKeysAndMalformedIntervals(): void
    {
        $service = new ScheduleImpactService($this->db);

        // Unknown top-level key
        try {
            $service->validateProposedProfileDto(['bogus_field' => 'data']);
            $this->fail('Unknown profile keys must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('invalidInput', $e->getMessage());
        }

        // Unknown weekday in weekly_hours
        try {
            $service->validateProposedProfileDto([
                'is_bookable' => true,
                'weekly_hours' => ['funday' => []],
            ]);
            $this->fail('Unknown weekday must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('invalidInput', $e->getMessage());
        }

        // Overlapping intervals in same day
        try {
            $service->validateProposedProfileDto([
                'is_bookable' => true,
                'weekly_hours' => [
                    'monday' => [
                        ['start' => '09:00', 'end' => '13:00'],
                        ['start' => '12:00', 'end' => '16:00'],
                    ],
                ],
            ]);
            $this->fail('Overlapping intervals must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('invalidInput', $e->getMessage());
        }
    }

    public function testPureIntervalValidationDstGapsAndFolds(): void
    {
        $tz = new \DateTimeZone('Asia/Jerusalem');

        // Normal compliant interval
        $valid = \App\Services\ClinicScheduleAvailabilityService::validateAppointmentInterval('2026-11-09', '10:00', 30, $tz);
        $this->assertNull($valid);

        foreach ([['2026-03-27','02:15','INVALID_DST_INTERVAL'],['2026-10-25','01:15','AMBIGUOUS_DST_INTERVAL']] as [$date,$time,$code]) {
            $result = \App\Services\ClinicScheduleAvailabilityService::validateAppointmentInterval($date,$time,30,$tz);
            $this->assertSame($code,$result['code'] ?? null);
        }

        // Crossing midnight
        $cross = \App\Services\ClinicScheduleAvailabilityService::validateAppointmentInterval('2026-11-09', '23:45', 30, $tz);
        $this->assertNotNull($cross);
        $this->assertSame('CROSS_MIDNIGHT', $cross['code']);

        // Invalid duration
        $badDur = \App\Services\ClinicScheduleAvailabilityService::validateAppointmentInterval('2026-11-09', '10:00', 0, $tz);
        $this->assertNotNull($badDur);
        $this->assertSame('INVALID_DURATION', $badDur['code']);
    }
}
