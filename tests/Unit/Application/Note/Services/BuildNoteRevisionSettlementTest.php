<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\DTO\NoteRevisionSettlement;
use App\Application\Note\Services\BuildNoteRevisionSettlement;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildNoteRevisionSettlementTest extends TestCase
{
    public function test_it_marks_equal_revision_total_as_paid(): void
    {
        $settlement = $this->builder()->build('set-1', 'rev-1', 'note-1', 200000, 200000, 0, $this->time());

        $this->assertSame(NoteRevisionSettlement::STATUS_PAID, $settlement->settlementStatus);
        $this->assertSame(200000, $settlement->netPaidRupiah);
        $this->assertSame(0, $settlement->outstandingRupiah);
        $this->assertSame(0, $settlement->surplusRupiah);
    }

    public function test_it_marks_upward_revision_as_underpaid(): void
    {
        $settlement = $this->builder()->build('set-1', 'rev-1', 'note-1', 250000, 200000, 0, $this->time());

        $this->assertSame(NoteRevisionSettlement::STATUS_UNDERPAID, $settlement->settlementStatus);
        $this->assertSame(50000, $settlement->outstandingRupiah);
        $this->assertSame(0, $settlement->surplusRupiah);
    }

    public function test_it_marks_downward_revision_surplus_as_overpaid_pending(): void
    {
        $settlement = $this->builder()->build('set-1', 'rev-1', 'note-1', 150000, 200000, 0, $this->time());

        $this->assertSame(NoteRevisionSettlement::STATUS_OVERPAID_PENDING, $settlement->settlementStatus);
        $this->assertSame(0, $settlement->outstandingRupiah);
        $this->assertSame(50000, $settlement->surplusRupiah);
    }

    public function test_it_subtracts_carried_forward_refunds_from_net_paid(): void
    {
        $settlement = $this->builder()->build('set-1', 'rev-1', 'note-1', 200000, 300000, 100000, $this->time());

        $this->assertSame(NoteRevisionSettlement::STATUS_PAID, $settlement->settlementStatus);
        $this->assertSame(200000, $settlement->netPaidRupiah);
        $this->assertSame(0, $settlement->outstandingRupiah);
        $this->assertSame(0, $settlement->surplusRupiah);
    }

    public function test_it_rejects_negative_input(): void
    {
        $this->expectException(DomainException::class);

        $this->builder()->build('set-1', 'rev-1', 'note-1', 200000, -1, 0, $this->time());
    }

    private function builder(): BuildNoteRevisionSettlement
    {
        return new BuildNoteRevisionSettlement();
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-05-13 10:00:00');
    }
}
