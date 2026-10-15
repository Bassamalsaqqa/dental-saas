<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AppointmentReschedulabilityService;
use App\Services\AppointmentSchedulingService;
use App\Services\AppointmentSlotEditContext;
use Tests\Support\PhaseBFixture;

final class AppointmentReschedulabilityPolicyTest extends PhaseBFixture
{
    private function book(array $extra = []): int
    {
        $result = (new AppointmentSchedulingService($this->db))->createForClinic($this->clinic, $this->payload($extra), $this->actor);
        $this->assertTrue($result['success'], json_encode($result));
        return (int) $result['id'];
    }

    public function testFutureScheduledAppointmentIsReschedulableAndProofIssuesAndResolves(): void
    {
        $id = $this->book();
        $this->assertTrue(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));

        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);
        $this->assertSame($id, $context->resolve($this->clinic, $id, $token));
    }

    public function testCompletedAppointmentDeniesReschedulingAndEditProofFailsClosed(): void
    {
        $id = $this->book();
        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);

        // Mark visit as completed
        $this->db->table('appointments')->where('id', $id)->update(['status' => 'completed']);

        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $this->assertNull($context->issue($this->clinic, $id));
        $this->assertNull($context->resolve($this->clinic, $id, $token));
    }

    public function testPastAppointmentDeniesReschedulingAndEditProofFailsClosed(): void
    {
        $id = $this->book();
        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);

        // Move appointment to yesterday
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $this->db->table('appointments')->where('id', $id)->update(['appointment_date' => $yesterday]);

        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $this->assertNull($context->issue($this->clinic, $id));
        $this->assertNull($context->resolve($this->clinic, $id, $token));
    }

    public function testInactivePatientDeniesReschedulingAndEditProofFailsClosed(): void
    {
        $id = $this->book();
        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);

        // Deactivate patient
        $this->db->table('patients')->where('id', $this->patient)->update(['status' => 'inactive']);

        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $this->assertNull($context->issue($this->clinic, $id));
        $this->assertNull($context->resolve($this->clinic, $id, $token));
    }

    public function testDynamicDisabledWithPopulatedProviderIsNotesOnlyAndDeniesProof(): void
    {
        $id = $this->book();
        // Disable dynamic doctor scheduling
        $this->setting('dynamic_doctor_scheduling_enabled', false);

        // Appointment still has populated provider_user_id
        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $context = new AppointmentSlotEditContext($this->db);
        $this->assertNull($context->issue($this->clinic, $id));
    }

    public function testDynamicDisabledWithNullProviderAllowsReschedulingProof(): void
    {
        $this->setting('dynamic_doctor_scheduling_enabled', false);
        $id = $this->book(['chair_name' => 'Legacy room', 'provider_user_id' => null]);

        $this->assertTrue(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);
        $this->assertSame($id, $context->resolve($this->clinic, $id, $token));
    }

    public function testClinicalLockTransitionsDenyResolveAfterLinkAdded(): void
    {
        // 1. Examination link
        $id1 = $this->book();
        $context = new AppointmentSlotEditContext($this->db);
        $token1 = $context->issue($this->clinic, $id1);
        $this->assertNotNull($token1);

        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'appointment_id' => $id1,
            'patient_id' => $this->patient,
            'examination_date' => '2026-11-09',
            'examination_id' => 'E'.bin2hex(random_bytes(8)),
            'chief_complaint' => 'Synthetic policy boundary',
            'created_by' => $this->actor,
        ]);
        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id1));
        $this->assertNull($context->resolve($this->clinic, $id1, $token1));

        // 2. Clinical encounter procedure link
        $id2 = $this->book(['appointment_time' => '11:00']);
        $token2 = $context->issue($this->clinic, $id2);
        $this->assertNotNull($token2);

        $this->db->table('clinical_procedures')->insert([
            'clinic_id' => $this->clinic,
            'appointment_id' => $id2,
            'patient_id' => $this->patient,
            'status' => 'completed',
        ]);
        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id2));
        $this->assertNull($context->resolve($this->clinic, $id2, $token2));

        // 3. Clinical charge line link
        $id3 = $this->book(['appointment_time' => '12:00']);
        $token3 = $context->issue($this->clinic, $id3);
        $this->assertNotNull($token3);

        $this->db->table('clinical_charge_lines')->insert([
            'clinic_id' => $this->clinic,
            'appointment_id' => $id3,
            'patient_id' => $this->patient,
            'unit_price' => '100.00',
        ]);
        $this->assertFalse(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id3));
        $this->assertNull($context->resolve($this->clinic, $id3, $token3));
    }

    public function testAcceptedProcedureFutureLinkAloneDoesNotLockRescheduling(): void
    {
        $id = $this->book(['appointment_time' => '14:00']);
        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);

        // Insert accepted procedure that was scheduled into this appointment
        // Note: appointment_id is NULL (no encounter occurred yet), scheduled_appointment_id is $id
        $this->db->table('clinical_procedures')->insert([
            'clinic_id' => $this->clinic,
            'appointment_id' => null,
            'scheduled_appointment_id' => $id,
            'patient_id' => $this->patient,
            'status' => 'accepted',
        ]);

        $this->assertTrue(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));
        $this->assertSame($id, $context->resolve($this->clinic, $id, $token));
    }

    public function testUnbookableOldProviderDoesNotDenyRescheduling(): void
    {
        $id = $this->book(['appointment_time' => '15:00']);

        // Create a real unavailable profile; an UPDATE against a nonexistent profile proves nothing.
        $this->db->table('clinic_provider_profiles')->insert(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0],'is_bookable'=>0,'is_clinician'=>0]);
        $this->assertNull((new \App\Services\ProviderDirectoryService($this->db))->findBookable($this->clinic,$this->providers[0]));

        // Explicit reassignment to another doctor or time is legitimate, so rescheduling is allowed
        $this->assertTrue(AppointmentReschedulabilityService::isReschedulable($this->db, $this->clinic, $id));

        $context = new AppointmentSlotEditContext($this->db);
        $token = $context->issue($this->clinic, $id);
        $this->assertNotNull($token);
        $this->assertSame($id, $context->resolve($this->clinic, $id, $token));
    }
    protected function tearDown(): void
    {
        if (isset($this->clinic)) {
            foreach (['examinations','clinical_charge_lines'] as $table) $this->db->table($table)->where('clinic_id',$this->clinic)->delete();
        }
        parent::tearDown();
    }

}
