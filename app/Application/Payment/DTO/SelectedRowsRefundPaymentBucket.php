<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Core\Shared\Exceptions\DomainException;

final class SelectedRowsRefundPaymentBucket
{
    /**
     * @param list<string> $rowIds
     */
    private function __construct(
        private readonly string $customerPaymentId,
        private readonly array $rowIds,
        private readonly int $amountRupiah,
    ) {
    }

    /**
     * @param list<string> $rowIds
     */
    public static function create(string $customerPaymentId, array $rowIds, int $amountRupiah): self
    {
        $paymentId = trim($customerPaymentId);
        $normalizedRowIds = array_values(array_unique(array_filter(
            $rowIds,
            static fn (string $id): bool => trim($id) !== '',
        )));

        if ($paymentId === '') {
            throw new DomainException('Customer payment id pada refund bucket wajib ada.');
        }

        if ($normalizedRowIds === []) {
            throw new DomainException('Refund bucket wajib memiliki minimal satu row id.');
        }

        if ($amountRupiah <= 0) {
            throw new DomainException('Nominal refund bucket harus lebih besar dari 0.');
        }

        return new self($paymentId, $normalizedRowIds, $amountRupiah);
    }

    public function customerPaymentId(): string
    {
        return $this->customerPaymentId;
    }

    /**
     * @return list<string>
     */
    public function rowIds(): array
    {
        return $this->rowIds;
    }

    public function amountRupiah(): int
    {
        return $this->amountRupiah;
    }

    /**
     * @return array{customer_payment_id: string, row_ids: list<string>, amount_rupiah: int}
     */
    public function toArray(): array
    {
        return [
            'customer_payment_id' => $this->customerPaymentId,
            'row_ids' => $this->rowIds,
            'amount_rupiah' => $this->amountRupiah,
        ];
    }
}
