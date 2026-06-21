<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Core\Shared\Exceptions\DomainException;

final class SelectedRowsRefundPlan
{
    /**
     * @param list<string> $selectedRowIds
     * @param list<string> $unpaidRowIds
     * @param list<SelectedRowsRefundPaymentBucket> $paymentBuckets
     * @param list<string> $cancellableRowIds
     */
    private function __construct(
        private readonly string $noteId,
        private readonly array $selectedRowIds,
        private readonly array $unpaidRowIds,
        private readonly array $paymentBuckets,
        private readonly array $cancellableRowIds,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     * @param list<string> $unpaidRowIds
     * @param list<SelectedRowsRefundPaymentBucket> $paymentBuckets
     * @param list<string> $cancellableRowIds
     */
    public static function create(
        string $noteId,
        array $selectedRowIds,
        array $unpaidRowIds,
        array $paymentBuckets,
        array $cancellableRowIds = [],
    ): self
    {
        $normalizedNoteId = trim($noteId);

        if ($normalizedNoteId === '') {
            throw new DomainException('Note id pada refund plan wajib ada.');
        }

        if ($selectedRowIds === []) {
            throw new DomainException('Refund plan wajib memiliki selected rows.');
        }

        return new self(
            $normalizedNoteId,
            array_values($selectedRowIds),
            array_values($unpaidRowIds),
            $paymentBuckets,
            array_values($cancellableRowIds),
        );
    }

    public function noteId(): string
    {
        return $this->noteId;
    }

    public function selectedRowIds(): array
    {
        return $this->selectedRowIds;
    }

    public function unpaidRowIds(): array
    {
        return $this->unpaidRowIds;
    }

    public function paymentBuckets(): array
    {
        return $this->paymentBuckets;
    }

    public function cancellableRowIds(): array
    {
        return $this->cancellableRowIds;
    }

    public function totalRefundRupiah(): int
    {
        return array_sum(array_map(
            static fn (SelectedRowsRefundPaymentBucket $bucket): int => $bucket->amountRupiah(),
            $this->paymentBuckets,
        ));
    }

    public function toArray(): array
    {
        return (new SelectedRowsRefundPlanArraySerializer())->serialize($this);
    }
}
