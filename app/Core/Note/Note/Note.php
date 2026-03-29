<?php

declare(strict_types=1);

namespace App\Core\Note\Note;

use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class Note
{
    use NoteState;
    use NoteValidation;

    public static function create(
        string $id,
        string $name,
        ?string $customerPhone,
        DateTimeImmutable $date,
    ): self {
        self::assertValidIdentity($id, $name);

        return new self(
            trim($id),
            trim($name),
            self::normalizeCustomerPhone($customerPhone),
            $date,
            [],
            Money::zero(),
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
    ): self {
        self::assertValidIdentity($id, $name);
        self::assertValidWorkItems($workItems);
        $total->ensureNotNegative('Total note tidak boleh negatif.');

        if ($workItems !== []) {
            if (! self::calculateTotalFromWorkItems($workItems)->equals($total)) {
                throw new DomainException('Total note tidak konsisten dengan subtotal work item.');
            }
        }

        return new self(
            trim($id),
            trim($name),
            self::normalizeCustomerPhone($customerPhone),
            $date,
            array_values($workItems),
            $total,
        );
    }

    public function addWorkItem(WorkItem $item): void
    {
        if ($item->noteId() !== $this->id) {
            throw new DomainException('Work item tidak belong ke note ini.');
        }

        foreach ($this->workItems as $existing) {
            if ($existing->id() === $item->id()) {
                throw new DomainException('Work item ID duplikat.');
            }

            if ($existing->lineNo() === $item->lineNo()) {
                throw new DomainException('Line number duplikat.');
            }
        }

        $this->workItems[] = $item;
        $this->totalRupiah = $this->totalRupiah->add($item->subtotalRupiah());
    }

    public function syncTotalRupiah(Money $total): void
    {
        $total->ensureNotNegative('Total note tidak boleh negatif.');
        $this->totalRupiah = $total;
    }

    private static function normalizeCustomerPhone(?string $customerPhone): ?string
    {
        if ($customerPhone === null) {
            return null;
        }

        $normalized = trim($customerPhone);

        return $normalized === '' ? null : $normalized;
    }
}
