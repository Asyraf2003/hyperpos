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
     */
    private function __construct(
        private readonly string $noteId,
        private readonly array $selectedRowIds,
        private readonly array $unpaidRowIds,
        private readonly array $paymentBuckets,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     * @param list<string> $unpaidRowIds
     * @param list<SelectedRowsRefundPaymentBucket> $paymentBuckets
     */
    public static function create(
        string $noteId,
        array $selectedRowIds,
        array $unpaidRowIds,
        array $paymentBuckets,
    ): self {
        $normalizedNoteId = trim($noteId);

        if ($normalizedNoteId === '') {
            throw new DomainException('Note id pada refund plan wajib ada.');
        }

        if ($selectedRowIds === []) {
            throw new DomainException('Refund plan wajib memiliki selected rows.');
        }

        return new self($normalizedNoteId, $selectedRowIds, $unpaidRowIds, $paymentBuckets);
    }

    public function noteId(): string
    {
        return $this->noteId;
    }

    /**
     * @return list<string>
     */
    public function selectedRowIds(): array
    {
        return $this->selectedRowIds;
    }

    /**
     * @return list<string>
     */
    public function unpaidRowIds(): array
    {
        return $this->unpaidRowIds;
    }

    /**
     * @return list<SelectedRowsRefundPaymentBucket>
     */
    public function paymentBuckets(): array
    {
        return $this->paymentBuckets;
    }

    public function totalRefundRupiah(): int
    {
        return array_sum(array_map(
            static fn (SelectedRowsRefundPaymentBucket $bucket): int => $bucket->amountRupiah(),
            $this->paymentBuckets,
        ));
    }

    /**
     * @return array{
     *   note_id: string,
     *   selected_row_ids: list<string>,
     *   unpaid_row_ids: list<string>,
     *   total_refund_rupiah: int,
     *   payment_buckets: list<array{customer_payment_id: string, row_ids: list<string>, amount_rupiah: int}>
     * }
     */
    public function toArray(): array
    {
        return [
            'note_id' => $this->noteId,
            'selected_row_ids' => $this->selectedRowIds,
            'unpaid_row_ids' => $this->unpaidRowIds,
            'total_refund_rupiah' => $this->totalRefundRupiah(),
            'payment_buckets' => array_map(
                static fn (SelectedRowsRefundPaymentBucket $bucket): array => $bucket->toArray(),
                $this->paymentBuckets,
            ),
        ];
    }
}
