<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\AppointmentSchedulingService;
use App\Services\ClinicSchedulingSettings;
use App\Services\ProviderDirectoryService;
use App\Services\TreatmentProcedureIntegrationService;
use Tests\Support\PhaseBFixture;

final class PhaseBBackendTest extends PhaseBFixture
{
    public function testDirectoryAutoEligibleDoctorsAndExcludesInactiveOrForeignRoles(): void
    {
        $directory = new ProviderDirectoryService($this->db);
        $providers = $directory->listForClinic($this->clinic);

        $this->assertGreaterThanOrEqual(4, count($providers));
        $ids = array_column($providers, 'user_id');
        $this->assertSame(count($ids), count(array_unique($ids)));

        // Deactivating user removes from bookable directory
        $this->db->table('users')->where('id', $this->providers[0])->update(['active' => 0]);
        $this->assertNull($directory->findBookable($this->clinic, $this->providers[0]));

        // Foreign role clinic removes from bookable directory
        $this->db->table('roles')->where('id', $this->role)->update(['clinic_id' => $this->otherClinic, 'is_doctor' => 1]);
        $this->db->table('clinic_users')->where(['clinic_id' => $this->clinic, 'user_id' => $this->providers[1]])->update(['role_id' => $this->role]);
        $this->assertNull($directory->findBookable($this->clinic, $this->providers[1]));
    }

    public function testScalarIdAndDurationBoundaries(): void
    {
        $service = new AppointmentSchedulingService($this->db);

        $badPayloads = [
            ['duration' => '30junk'],
            ['duration' => 0],
            ['duration' => 481],
            ['provider_user_id' => [$this->providers[0]]],
            ['provider_user_id' => '1e2'],
            ['provider_user_id' => $this->actor],
            ['patient_id' => 'invalid'],
            ['patient_id' => [$this->patient]],
        ];

        foreach ($badPayloads as $bad) {
            $result = $service->createForClinic($this->clinic, $this->payload($bad), $this->actor);
            $this->assertFalse($result['success'], json_encode($bad) . ' unexpected success: ' . json_encode($result));
        }

        $this->assertSame(0, $this->db->table('appointments')->where('clinic_id', $this->clinic)->countAllResults());
    }

    public function testIsoDateAndTimeAndMidnightCrossing(): void
    {
        $service = new AppointmentSchedulingService($this->db);

        $badDates = [
            ['appointment_date' => '2026-02-30'],
            ['appointment_date' => 'not-a-date'],
            ['appointment_time' => '24:10'],
            ['appointment_time' => '12:60'],
            ['appointment_time' => '23:45', 'duration' => 60],
        ];

        foreach ($badDates as $bad) {
            $result = $service->createForClinic($this->clinic, $this->payload($bad), $this->actor);
            $this->assertFalse($result['success'], json_encode($bad) . ' unexpected success');
        }

        $this->assertSame(0, $this->db->table('appointments')->where('clinic_id', $this->clinic)->countAllResults());
    }

    public function testReschedulingEligibilityAndLockedPastVisitRules(): void
    {
        $service = new AppointmentSchedulingService($this->db);

        // 1. Create a future appointment
        $future = $service->createForClinic($this->clinic, $this->payload([
            'appointment_date' => '2026-11-09',
            'appointment_time' => '10:00',
        ]), $this->actor);
        $this->assertTrue($future['success']);
        $aptId = (int) $future['id'];

        // 2. Future appointment can be rescheduled
        $reschedule = $service->updateForClinic($this->clinic, $aptId, $this->payload([
            'appointment_date' => '2026-11-09',
            'appointment_time' => '11:00',
        ]), $this->actor);
        $this->assertTrue($reschedule['success'], json_encode($reschedule));
        $this->assertSame('11:00', substr($reschedule['appointment']['appointment_time'], 0, 5));

        // 3. Attempting to change patient must fail
        $wrongPatient = $service->updateForClinic($this->clinic, $aptId, $this->payload([
            'patient_id' => 999999,
        ]), $this->actor);
        $this->assertFalse($wrongPatient['success']);
        $this->assertSame('PATIENT_IMMUTABLE', $wrongPatient['code']);

        // 4. Attempting to change status via update must fail
        $wrongStatus = $service->updateForClinic($this->clinic, $aptId, $this->payload([
            'status' => 'cancelled',
        ]), $this->actor);
        $this->assertFalse($wrongStatus['success']);
        $this->assertSame('STATUS_TRANSITION_FORBIDDEN', $wrongStatus['code']);

        // 5. Backdate to past visit
        $this->db->table('appointments')->where('id', $aptId)->update(['appointment_date' => '2020-01-01']);

        // 6. Changing schedule on past visit must fail
        $modifyPastSchedule = $service->updateForClinic($this->clinic, $aptId, $this->payload([
            'appointment_date' => '2026-11-10',
        ]), $this->actor);
        $this->assertFalse($modifyPastSchedule['success']);
        $this->assertSame('PAST_APPOINTMENT_LOCKED', $modifyPastSchedule['code']);

        // 7. Modifying notes on past visit succeeds without changing schedule
        $updatePastNotes = $service->updateForClinic($this->clinic, $aptId, [
            'notes' => 'Updated clinical notes for past visit',
        ], $this->actor);
        $this->assertTrue($updatePastNotes['success'], json_encode($updatePastNotes));
        $this->assertSame('Updated clinical notes for past visit', $updatePastNotes['appointment']['notes']);
        $this->assertSame('2020-01-01', $updatePastNotes['appointment']['appointment_date']);
    }

    public function testAtomicAcceptedProcedureLinkage(): void
    {
        $service = new AppointmentSchedulingService($this->db);

        // Insert accepted procedure
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinical_procedures')->insert([
            'clinic_id'      => $this->clinic,
            'patient_id'     => $this->patient,
            'procedure_name' => 'Synthetic Procedure',
            'status'         => 'accepted',
            'created_by'     => $this->actor,
            'updated_by'     => $this->actor,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        $procedureId = (int) $this->db->insertID();

        // Create appointment with procedure linkage
        $result = $service->createForClinic($this->clinic, $this->payload([
            'appointment_time'      => '14:00',
            'clinical_procedure_id' => $procedureId,
        ]), $this->actor);

        $this->assertTrue($result['success'], json_encode($result));
        $aptId = (int) $result['id'];

        // Verify procedure is scheduled and linked to appointment
        $procedure = $this->db->table('clinical_procedures')->where('id', $procedureId)->get()->getRowArray();
        $this->assertSame('scheduled', $procedure['status']);
        $this->assertSame($aptId, (int) $procedure['scheduled_appointment_id']);
    }

    public function testAuditLogWrittenInTransaction(): void
    {
        $service = new AppointmentSchedulingService($this->db);
        $result = $service->createForClinic($this->clinic, $this->payload([
            'appointment_time' => '15:00',
        ]), $this->actor);

        $this->assertTrue($result['success']);
        $aptId = (int) $result['id'];

        $log = $this->db->table('activity_logs')
            ->where('clinic_id', $this->clinic)
            ->where('entity_type', 'appointment')
            ->where('entity_id', $aptId)
            ->get()
            ->getRowArray();

        $this->assertNotNull($log);
        $this->assertSame($this->actor, (int) $log['user_id']);
        $this->assertSame('create', $log['action']);
    }

    public function testPublicApiCreateUpdateDelete405AndInvalidJson422(): void
    {
        $token = (new \App\Models\ApiTokenModel())->generateToken($this->actor, 'backend-test-token');

        // 1. DELETE unsupported 405
        $del = $this->withSession([])->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Clinic-Id'   => (string) $this->clinic,
        ])->delete('/api/v1/appointments/123');
        $del->assertStatus(405);
        $delJson = json_decode($del->response()->getBody(), true);
        $this->assertSame(405, $delJson['status']);

        // 2. Malformed JSON payload returns 422
        $badJson = $this->withSession([])->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Clinic-Id'   => (string) $this->clinic,
            'Content-Type'  => 'application/json',
        ])->withBody('{invalid-json')->post('/api/v1/appointments');
        $badJson->assertStatus(422);

        // 3. Valid JSON creation returns 201
        $create = $this->withSession([])->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Clinic-Id'   => (string) $this->clinic,
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode($this->payload([
            'appointment_time' => '16:00',
        ])))->post('/api/v1/appointments');

        $create->assertStatus(201);
        $this->assertFalse($create->isRedirect());
        $createData = json_decode($create->response()->getBody(), true);
        $this->assertNotNull($createData['id'] ?? null);
    }
}
