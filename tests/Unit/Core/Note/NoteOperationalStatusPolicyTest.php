<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Note;

use App\Core\Note\Note\NoteOperationalStatusPolicy;
use PHPUnit\Framework\TestCase;

final class NoteOperationalStatusPolicyTest extends TestCase
{
    public function test_it_keeps_zero_total_note_open(): void
    {
        $policy = new NoteOperationalStatusPolicy();

        $this->assertSame(NoteOperationalStatusPolicy::STATUS_OPEN, $policy->resolve(0, 0));
        $this->assertTrue($policy->isOpen(0, 0));
        $this->assertFalse($policy->isClose(0, 0));
    }

    public function test_it_closes_only_when_net_paid_reaches_positive_total(): void
    {
        $policy = new NoteOperationalStatusPolicy();

        $this->assertSame(NoteOperationalStatusPolicy::STATUS_OPEN, $policy->resolve(50000, 49999));
        $this->assertSame(NoteOperationalStatusPolicy::STATUS_CLOSE, $policy->resolve(50000, 50000));
        $this->assertSame(NoteOperationalStatusPolicy::STATUS_CLOSE, $policy->resolve(50000, 60000));
    }
}
