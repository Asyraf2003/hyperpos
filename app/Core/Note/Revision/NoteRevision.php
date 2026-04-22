<?php

declare(strict_types=1);

namespace App\Core\Note\Revision;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class NoteRevision
{
    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     */
    private function __construct(
        private string $id,
        private string $noteRootId,
        private int $revisionNumber,
        private ?string $parentRevisionId,
        private ?string $createdByActorId,
        private ?string $reason,
        private string $customerName,
        private ?string $customerPhone,
        private DateTimeImmutable $transactionDate,
        private int $grandTotalRupiah,
        private array $lines,
        private DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     */
    public static function create(
        string $id,
        string $noteRootId,
        int $revisionNumber,
        ?string $parentRevisionId,
        ?string $createdByActorId,
        ?string $reason,
        string $customerName,
        ?string $customerPhone,
        DateTimeImmutable $transactionDate,
        int $grandTotalRupiah,
        array $lines,
        DateTimeImmutable $createdAt,
    ): self {
        $id = trim($id);
        $noteRootId = trim($noteRootId);
        $customerName = trim($customerName);

        if ($id === '') {
            throw new DomainException('Note revision id wajib diisi.');
        }

        if ($noteRootId === '') {
            throw new DomainException('Note root id wajib diisi.');
        }

        if ($revisionNumber <= 0) {
            throw new DomainException('Revision number wajib lebih dari nol.');
        }

        if ($customerName === '') {
            throw new DomainException('Customer name revision wajib diisi.');
        }

        if ($grandTotalRupiah < 0) {
            throw new DomainException('Grand total revision tidak boleh negatif.');
        }

        foreach ($lines as $line) {
            if (! $line instanceof NoteRevisionLineSnapshot) {
                throw new DomainException('Semua line revision wajib berupa NoteRevisionLineSnapshot.');
            }

            if ($line->noteRevisionId() !== $id) {
                throw new DomainException('Semua line revision wajib belong ke note revision yang sama.');
            }
        }

        return new self(
            $id,
            $noteRootId,
            $revisionNumber,
            $parentRevisionId !== null && trim($parentRevisionId) !== '' ? trim($parentRevisionId) : null,
            $createdByActorId !== null && trim($createdByActorId) !== '' ? trim($createdByActorId) : null,
            $reason !== null && trim($reason) !== '' ? trim($reason) : null,
            $customerName,
            $customerPhone !== null && trim($customerPhone) !== '' ? trim($customerPhone) : null,
            $transactionDate,
            $grandTotalRupiah,
            array_values($lines),
            $createdAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function noteRootId(): string
    {
        return $this->noteRootId;
    }

    public function revisionNumber(): int
    {
        return $this->revisionNumber;
    }

    public function parentRevisionId(): ?string
    {
        return $this->parentRevisionId;
    }

    public function createdByActorId(): ?string
    {
        return $this->createdByActorId;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function customerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function transactionDate(): DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function grandTotalRupiah(): int
    {
        return $this->grandTotalRupiah;
    }

    /**
     * @return list<NoteRevisionLineSnapshot>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function lineCount(): int
    {
        return count($this->lines);
    }
}
