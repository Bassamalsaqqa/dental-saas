<?php

namespace Tests\Support;

use App\Services\AppointmentVisitStageService;
use CodeIgniter\Test\CIUnitTestCase;

final class AppointmentVisitStageServiceTest extends CIUnitTestCase
{
    private AppointmentVisitStageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentVisitStageService();
    }

    public function testNormalVisitCanAdvanceToCompletion(): void
    {
        $path = ['scheduled', 'confirmed', 'arrived', 'ready', 'in_chair', 'checkout', 'completed'];
        for ($index = 0; $index < count($path) - 1; $index++) {
            $this->assertTrue($this->service->canTransition($path[$index], $path[$index + 1]));
        }
    }

    public function testOperationalStagesCannotSkipDirectlyToCompleted(): void
    {
        $this->assertFalse($this->service->canTransition('scheduled', 'completed'));
        $this->assertFalse($this->service->canTransition('arrived', 'completed'));
        $this->assertFalse($this->service->canTransition('ready', 'completed'));
    }

    public function testConsultationCanCompleteFromChair(): void
    {
        $this->assertTrue($this->service->canTransition('in_chair', 'completed'));
    }

    public function testTerminalStagesCannotBeReopenedImplicitly(): void
    {
        foreach (['completed', 'cancelled', 'no_show'] as $stage) {
            $this->assertSame([], $this->service->allowedNext($stage));
            $this->assertTrue($this->service->isTerminal($stage));
        }
    }

    public function testCancellationAndNoShowAreStageRestricted(): void
    {
        $this->assertTrue($this->service->canTransition('scheduled', 'no_show'));
        $this->assertTrue($this->service->canTransition('confirmed', 'no_show'));
        $this->assertFalse($this->service->canTransition('in_chair', 'no_show'));
        $this->assertTrue($this->service->canTransition('ready', 'cancelled'));
        $this->assertFalse($this->service->canTransition('checkout', 'cancelled'));
    }
}
