<?php

declare(strict_types=1);

namespace App\Application\Note\Policies;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class CashierNoteAccessGuard
{
    public function assertCanView(Note $note, DateTimeImmutable $today): void
    {
        if (! $this->isWithinCashierDateWindow($note, $today)) {
            throw new DomainException('Kasir hanya boleh mengakses note untuk hari ini dan kemarin.');
        }

        if ($note->isClosed()) {
            throw new DomainException('Kasir tidak boleh membuka detail note yang sudah ditutup.');
        }
    }

    public function assertCanMutateOpenNote(Note $note, DateTimeImmutable $today): void
    {
        $this->assertCanView($note, $today);
    }

    public function assertCanAccess(Note $note, DateTimeImmutable $today): void
    {
        $this->assertCanMutateOpenNote($note, $today);
    }

    private function isWithinCashierDateWindow(Note $note, DateTimeImmutable $today): bool
    {
        $noteDate = $note->transactionDate()->format('Y-m-d');
        $todayDate = $today->format('Y-m-d');
        $yesterdayDate = $today->modify('-1 day')->format('Y-m-d');

        return in_array($noteDate, [$todayDate, $yesterdayDate], true);
    }
}
