<?php

declare(strict_types=1);

namespace App\Core\Note\Revision\Concerns;

trait NoteRevisionLineSnapshotAccessors
{
    public function id(): string
    {
        return $this->id;
    }

    public function noteRevisionId(): string
    {
        return $this->noteRevisionId;
    }

    public function workItemRootId(): ?string
    {
        return $this->workItemRootId;
    }

    public function lineNo(): int
    {
        return $this->lineNo;
    }

    public function transactionType(): string
    {
        return $this->transactionType;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function subtotalRupiah(): int
    {
        return $this->subtotalRupiah;
    }

    public function serviceLabel(): ?string
    {
        return $this->serviceLabel;
    }

    public function servicePriceRupiah(): ?int
    {
        return $this->servicePriceRupiah;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
