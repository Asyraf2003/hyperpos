<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NotePseudoVersioningBuilder;
use PHPUnit\Framework\TestCase;

final class NotePseudoVersioningBuilderTest extends TestCase
{
    public function test_it_builds_current_state_and_baseline_from_oldest_history(): void
    {
        $builder = new NotePseudoVersioningBuilder();

        $result = $builder->build(
            ['note_state' => 'open', 'grand_total_rupiah' => 90000, 'net_paid_rupiah' => 30000, 'total_refunded_rupiah' => 0, 'outstanding_rupiah' => 60000, 'refund_required_rupiah' => 0],
            ['summary_label' => '1 Open'],
            [
                ['event_label' => 'Mutasi Baru', 'created_at' => '2026-04-21 12:00:00', 'before_total_rupiah' => 95000, 'after_total_rupiah' => 90000, 'refund_required_rupiah' => 0],
                ['event_label' => 'Mutasi Lama', 'created_at' => '2026-04-20 10:00:00', 'before_total_rupiah' => 100000, 'after_total_rupiah' => 95000, 'refund_required_rupiah' => 5000, 'target_status' => 'open'],
            ],
        );

        self::assertSame('open', $result['current']['note_state']);
        self::assertSame('1 Open', $result['current']['line_summary_label']);
        self::assertNotNull($result['baseline']);
        self::assertSame(100000, $result['baseline']['total_rupiah']);
        self::assertSame(5000, $result['baseline']['refund_required_rupiah']);
        self::assertSame('2026-04-20 10:00:00', $result['baseline']['captured_at']);
        self::assertCount(2, $result['timeline']);
    }

    public function test_it_keeps_baseline_null_when_history_is_empty_or_before_total_is_zero(): void
    {
        $builder = new NotePseudoVersioningBuilder();

        $empty = $builder->build([], [], []);
        self::assertNull($empty['baseline']);
        self::assertSame('Belum ada line.', $empty['current']['line_summary_label']);

        $zeroBefore = $builder->build(
            ['note_state' => 'closed'],
            ['summary_label' => '1 Close'],
            [['event_label' => 'Mutasi', 'created_at' => '2026-04-21 10:00:00', 'before_total_rupiah' => 0, 'after_total_rupiah' => 1000]],
        );

        self::assertNull($zeroBefore['baseline']);
        self::assertCount(1, $zeroBefore['timeline']);
    }
}
