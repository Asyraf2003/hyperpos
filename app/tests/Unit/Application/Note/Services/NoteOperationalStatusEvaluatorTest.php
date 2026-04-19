<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteOperationalStatusEvaluator;
use PHPUnit\Framework\TestCase;

final class NoteOperationalStatusEvaluatorTest extends TestCase
{
    private NoteOperationalStatusEvaluator $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new NoteOperationalStatusEvaluator();
    }

    public function test_it_returns_open_when_total_is_zero(): void
    {
        $this->assertSame(NoteOperationalStatusEvaluator::STATUS_OPEN, $this->service->resolve(0, 0));
        $this->assertTrue($this->service->isOpen(0, 0));
        $this->assertFalse($this->service->isClose(0, 0));
    }

    public function test_it_returns_open_when_net_paid_is_below_total(): void
    {
        $this->assertSame(NoteOperationalStatusEvaluator::STATUS_OPEN, $this->service->resolve(50000, 20000));
        $this->assertTrue($this->service->isOpen(50000, 20000));
        $this->assertFalse($this->service->isClose(50000, 20000));
    }

    public function test_it_returns_close_when_net_paid_equals_total(): void
    {
        $this->assertSame(NoteOperationalStatusEvaluator::STATUS_CLOSE, $this->service->resolve(50000, 50000));
        $this->assertFalse($this->service->isOpen(50000, 50000));
        $this->assertTrue($this->service->isClose(50000, 50000));
    }

    public function test_it_returns_close_when_net_paid_exceeds_total(): void
    {
        $this->assertSame(NoteOperationalStatusEvaluator::STATUS_CLOSE, $this->service->resolve(50000, 60000));
        $this->assertFalse($this->service->isOpen(50000, 60000));
        $this->assertTrue($this->service->isClose(50000, 60000));
    }
}
