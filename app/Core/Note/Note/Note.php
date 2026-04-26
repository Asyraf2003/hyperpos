<?php

declare(strict_types=1);

namespace App\Core\Note\Note;

use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class Note
{
    public const STATE_OPEN = 'open';
    public const STATE_CLOSED = 'closed';
    public const STATE_REFUNDED = 'refunded';

    use NoteState;
    use NoteValidation;
    use NoteMutations;
    use NoteNormalization;
    use NoteOperationalStateMutations;

    public static function create(string $id, string $name, ?string $customerPhone, DateTimeImmutable $date): self
    {
        self::assertValidIdentity($id, $name);

        return new self(
            trim($id),
            trim($name),
            self::normalizeCustomerPhone($customerPhone),
            $date,
            self::calculateDueDate($date),
            [],
            Money::zero(),
            self::STATE_OPEN,
            null,
            null,
            null,
            null,
        );
    }

    /** @param list<WorkItem> $workItems */
    public static function rehydrate(
        string $id,
        string $name,
        ?string $customerPhone,
        DateTimeImmutable $date,
        Money $total,
        array $workItems = [],
        string $noteState = self::STATE_OPEN,
        ?DateTimeImmutable $closedAt = null,
        ?string $closedByActorId = null,
        ?DateTimeImmutable $reopenedAt = null,
        ?string $reopenedByActorId = null,
        ?DateTimeImmutable $dueDate = null,
    ): self {
        self::assertValidIdentity($id, $name);
        self::assertValidWorkItems($workItems);
        self::assertValidOperationalState($noteState);
        $total->ensureNotNegative('Total note tidak boleh negatif.');

        if ($workItems !== [] && !self::calculateTotalFromWorkItems($workItems)->equals($total)) {
            throw new DomainException('Total note tidak konsisten dengan subtotal work item.');
        }

        return new self(
            trim($id),
            trim($name),
            self::normalizeCustomerPhone($customerPhone),
            $date,
            $dueDate ?? self::calculateDueDate($date),
            array_values($workItems),
            $total,
            trim($noteState),
            $closedAt,
            self::normalizeActorId($closedByActorId),
            $reopenedAt,
            self::normalizeActorId($reopenedByActorId),
        );
    }

    private static function calculateDueDate(DateTimeImmutable $transactionDate): DateTimeImmutable
    {
        $month = (int) $transactionDate->format('n') + 1;
        $year = (int) $transactionDate->format('Y');

        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $lastDay = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))
            ->modify('last day of this month')
            ->format('j');

        return $transactionDate->setDate($year, $month, min((int) $transactionDate->format('j'), $lastDay));
    }
}
