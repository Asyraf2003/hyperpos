<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Payment\Services\RecordCustomerRefundTransaction;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;

final class RecordCustomerRefundHandler
{
    use RecordCustomerRefundSupportTrait;

    public function __construct(
        private readonly RecordCustomerRefundTransaction $transaction,
    ) {
    }

    /** @param list<string> $selectedRowIds */
    public function handle(
        string $customerPaymentId,
        string $noteId,
        int $amountRupiah,
        string $refundedAt,
        string $reason,
        string $performedByActorId,
        array $selectedRowIds = [],
    ): Result {
        if (trim($reason) === '') {
            return Result::failure('Alasan refund wajib diisi.', ['refund' => ['AUDIT_REASON_REQUIRED']]);
        }

        if (trim($performedByActorId) === '') {
            return $this->classify(new DomainException('Actor refund wajib ada.'));
        }

        return $this->transaction->run(
            $customerPaymentId,
            $noteId,
            $amountRupiah,
            $refundedAt,
            $reason,
            $performedByActorId,
            $selectedRowIds,
        );
    }
}
