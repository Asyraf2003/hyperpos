<?php

declare(strict_types=1);

namespace App\Core\Note\Note;

use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait NoteState
{
    /** @param list<WorkItem> $workItems */
    private function __construct(
        private string $id,
        private string $customerName,
        private DateTimeImmutable $transactionDate,
        private array $workItems,
        private Money $totalRupiah,
    ) {}

    public function id(): string { return $this->id; }
    public function customerName(): string { return $this->customerName; }
    public function transactionDate(): DateTimeImmutable { return $this->transactionDate; }
    /** @return list<WorkItem> */
    public function workItems(): array { return $this->workItems; }
    public function totalRupiah(): Money { return $this->totalRupiah; }
}
