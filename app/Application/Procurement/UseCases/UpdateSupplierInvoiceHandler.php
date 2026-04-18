<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\UpdateSupplierInvoiceOperation;
use App\Application\Procurement\Services\VoidedSupplierInvoiceGuard;
use App\Application\Procurement\Services\UpdateSupplierInvoiceTransactionalRunner;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;

final class UpdateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $reader,
        private readonly SupplierInvoiceEditabilityGuard $guard,
        private readonly UpdateSupplierInvoiceTransactionalRunner $transactionalRunner,
        private readonly UpdateSupplierInvoiceOperation $operation,
        private readonly VoidedSupplierInvoiceGuard $voidedGuard,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
        ?int $expectedRevisionNo = null,
        ?string $changeReason = null,
        ?string $performedByActorId = null,
        ?string $performedByActorRole = null,
        string $sourceChannel = 'web_admin',
    ): Result {
        $current = $this->reader->getById($supplierInvoiceId);

        if ($current === null) {
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        $voided = $this->voidedGuard->ensureNotVoided($supplierInvoiceId);

        if ($voided->isFailure()) {
            return $voided;
        }

        $isRevisionMode = $expectedRevisionNo !== null && $changeReason !== null && trim($changeReason) !== '';

        if (! $isRevisionMode) {
            $editable = $this->guard->ensureEditable($supplierInvoiceId);

            if ($editable->isFailure()) {
                return $editable;
            }
        }

        return $this->transactionalRunner->run(
            fn (): Result => $this->operation->execute(
                $current,
                $supplierInvoiceId,
                $nomorFaktur,
                $namaPtPengirim,
                $tanggalPengiriman,
                $lines,
            ),
            $performedByActorId,
            $performedByActorRole,
            $sourceChannel,
        );
    }
}
