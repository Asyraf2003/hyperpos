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

    use NoteState;
    use NoteValidation;
    use NoteMutations;
    use NoteOperationalStateMutations;

    public static function create(string $id, string $name, ?string $customerPhone, DateTimeImmutable $date): self
    {
        self::assertValidIdentity($id, $name);

        return new self(
            trim($id),
            trim($name),
            self::normalizeCustomerPhone($customerPhone),
            $date,
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
            array_values($workItems),
            $total,
            trim($noteState),
            $closedAt,
            self::normalizeActorId($closedByActorId),
            $reopenedAt,
            self::normalizeActorId($reopenedByActorId),
        );
    }

    private static function assertValidOperationalState(string $noteState): void
    {
        if (!in_array(trim($noteState), [self::STATE_OPEN, self::STATE_CLOSED], true)) {
            throw new DomainException('State operasional note tidak valid.');
        }
    }

    private static function normalizeCustomerPhone(?string $customerPhone): ?string
    {
        if ($customerPhone === null) {
            return null;
        }

        $normalized = trim($customerPhone);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeActorId(?string $actorId): ?string
    {
        if ($actorId === null) {
            return null;
        }

        $normalized = trim($actorId);

        return $normalized === '' ? null : $normalized;
    }
}
