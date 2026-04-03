<?php

declare(strict_types=1);

namespace App\Application\Note\Policies;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class CashierNoteAccessGuard
{
    public function assertCanAccess(Note $note, DateTimeImmutable $today): void
    {
        if ($note->isClosed()) {
            throw new DomainException('Kasir tidak boleh mengakses note yang sudah ditutup.');
        }

        $noteDate = $note->transactionDate()->format('Y-m-d');
        $todayDate = $today->format('Y-m-d');
        $yesterdayDate = $today->modify('-1 day')->format('Y-m-d');

        if (!in_array($noteDate, [$todayDate, $yesterdayDate], true)) {
            throw new DomainException('Kasir hanya boleh mengakses note open untuk hari ini dan kemarin.');
        }
    }
}
