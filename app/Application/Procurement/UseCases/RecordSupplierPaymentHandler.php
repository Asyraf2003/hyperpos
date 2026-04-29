<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\RecordSupplierPaymentUnderLock;
use App\Application\Procurement\Services\SupplierPaymentPreflight;
use App\Application\Shared\DTO\Result;

final class RecordSupplierPaymentHandler
{
    public function __construct(
        private readonly SupplierPaymentPreflight $preflight,
        private readonly RecordSupplierPaymentUnderLock $recordPayment,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $performedByActorId,
    ): Result {
        $prepared = $this->preflight->prepare(
            $supplierInvoiceId,
            $amountRupiah,
            $paidAt,
            $performedByActorId,
        );

        if ($prepared->isFailure()) {
            return $prepared;
        }

        $data = $prepared->data();

        return $this->recordPayment->execute(
            trim($supplierInvoiceId),
            $amountRupiah,
            $data['paid_at'],
            (string) $data['actor_id'],
        );
    }
}
