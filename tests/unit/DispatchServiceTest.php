<?php

namespace Tests\Unit;

use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Services\DispatchService;
use App\Services\NotificationDrivers\EmailDriver;
use App\Services\PlanGuard;
use CodeIgniter\Test\CIUnitTestCase;

final class DispatchServiceTest extends CIUnitTestCase
{
    public function testClaimNotificationReclaimsStaleProcessingLock(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for DispatchService claim test.');
        }

        $notificationModel = new NotificationModel();
        $notificationId = 0;

        try {
            $notificationId = (int) $notificationModel->withoutTenantScope()->insert([
                'clinic_id' => $clinicId,
                'channel_type' => 'email',
                'recipient_type' => 'external',
                'recipient_address' => 'dispatch-claim-test@example.com',
                'payload_json' => json_encode(['subject' => 'x', 'message' => 'y']),
                'status' => 'processing',
                'locked_at' => date('Y-m-d H:i:s', time() - 3600),
                'locked_by' => 'old-dispatcher',
                'initiated_by_user_id' => null,
                'job_audit_id' => null,
                'parent_notification_id' => null,
            ]);
            $this->assertGreaterThan(0, $notificationId);

            $service = new DispatchService();
            $claimed = $service->claimNotification($notificationId, $clinicId, 'phpunit-dispatcher', 'email');

            $this->assertTrue($claimed);

            $row = $notificationModel->withoutTenantScope()->where('id', $notificationId)->first();
            $this->assertIsArray($row);
            $this->assertSame('processing', $row['status']);
            $this->assertSame('phpunit-dispatcher', $row['locked_by']);
            $this->assertNotNull($row['locked_at']);
        } finally {
            if ($notificationId > 0) {
                $notificationModel->withoutTenantScope()->where('id', $notificationId)->delete();
            }
        }
    }

    public function testDispatchPendingFinalizesNotificationAndClearsLocksOnDriverException(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for DispatchService exception finalization test.');
        }

        $notificationModel = new NotificationModel();
        $attemptModel = new NotificationAttemptModel();
        $notificationId = 0;
        $session = session();
        $originalClinicId = $session->get('active_clinic_id');

        try {
            $session->set('active_clinic_id', $clinicId);

            $notificationId = (int) $notificationModel->withoutTenantScope()->insert([
                'clinic_id' => $clinicId,
                'channel_type' => 'email',
                'recipient_type' => 'external',
                'recipient_address' => 'dispatch-exception-test@example.com',
                'payload_json' => json_encode(['subject' => 'x', 'message' => 'y']),
                'status' => 'pending',
                'locked_at' => null,
                'locked_by' => null,
                'initiated_by_user_id' => null,
                'job_audit_id' => null,
                'parent_notification_id' => null,
            ]);
            $this->assertGreaterThan(0, $notificationId);

            $service = new class extends DispatchService {
                public function claimNotification(int $notificationId, int $clinicId, string $dispatcherId, string $channelType): bool
                {
                    return true;
                }
            };

            $this->setProperty($service, 'planGuard', new class extends PlanGuard {
                public function __construct()
                {
                }

                public function assertFeature(int $clinicId, string $featureKey, array $meta = [])
                {
                    return ['allowed' => true];
                }
            });

            $this->setProperty($service, 'channelModel', new class extends ClinicNotificationChannelModel {
                public function __construct()
                {
                }

                public function where($key = null, $value = null, ?bool $escape = null)
                {
                    return $this;
                }

                public function first()
                {
                    return [
                        'clinic_id' => 1,
                        'channel_type' => 'email',
                        'enabled_by_superadmin' => 1,
                        'configured_by_clinic' => 1,
                        'validated' => 1,
                    ];
                }

                public function getConfig(int $clinicId, string $channelType)
                {
                    return [
                        'smtp_host' => 'smtp.test.local',
                        'smtp_user' => 'user',
                        'smtp_pass' => 'pass',
                        'smtp_port' => 587,
                    ];
                }
            });

            $this->setProperty($service, 'emailDriver', new class extends EmailDriver {
                public function __construct()
                {
                }

                public function send(array $notification, int $clinicId, array $config): array
                {
                    throw new \RuntimeException('Synthetic driver failure');
                }
            });

            $summary = $service->dispatchPendingForClinic($clinicId, 'email', 10, 'phpunit-dispatch');

            $this->assertSame(1, $summary['processed']);
            $this->assertSame(1, $summary['claimed']);
            $this->assertSame(1, $summary['failed']);

            $row = $notificationModel->withoutTenantScope()->where('id', $notificationId)->first();
            $this->assertIsArray($row);
            $this->assertSame('failed', $row['status']);
            $this->assertSame('DISPATCH_RUNTIME_EXCEPTION', $row['failure_reason']);
            $this->assertNull($row['locked_at']);
            $this->assertNull($row['locked_by']);

            $attemptRows = $attemptModel->withoutTenantScope()->where('notification_id', $notificationId)->findAll();
            $this->assertCount(1, $attemptRows);
            $this->assertSame('sending', $attemptRows[0]['status']);
        } finally {
            $session->set('active_clinic_id', $originalClinicId);
            if ($notificationId > 0) {
                $attemptModel->withoutTenantScope()->where('notification_id', $notificationId)->delete();
                $notificationModel->withoutTenantScope()->where('id', $notificationId)->delete();
            }
        }
    }

    private function findAnyClinicId(): int
    {
        $db = \Config\Database::connect('tests');
        $row = $db->table('clinics')->select('id')->orderBy('id', 'ASC')->get(1)->getRowArray();

        return (int) ($row['id'] ?? 0);
    }

    private function setProperty(object $object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getProperty($property);
        $target->setAccessible(true);
        $target->setValue($object, $value);
    }
}
