<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\UpdateSupplierInvoiceOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class UpdateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $reader,
        private readonly SupplierInvoiceEditabilityGuard $guard,
        private readonly TransactionManagerPort $transactions,
        private readonly SupplierInvoiceChangeContext $changeContext,
        private readonly UpdateSupplierInvoiceOperation $operation,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
        ?string $performedByActorId = null,
        ?string $performedByActorRole = null,
        string $sourceChannel = 'web_admin',
    ): Result {
        $current = $this->reader->getById($supplierInvoiceId);

        if ($current === null) {
            return Result::failure('Nota supplier tidak ditemukan.', ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
        }

        $editable = $this->guard->ensureEditable($supplierInvoiceId);
        if ($editable->isFailure()) {
            return $editable;
        }

        return $this->runTransaction(
            $current,
            $supplierInvoiceId,
            $nomorFaktur,
            $namaPtPengirim,
            $tanggalPengiriman,
            $lines,
            $performedByActorId,
            $performedByActorRole,
            $sourceChannel,
        );
    }

    private function runTransaction(
        mixed $current,
        string $supplierInvoiceId,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
        ?string $performedByActorId,
        ?string $performedByActorRole,
        string $sourceChannel,
    ): Result {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;
            $this->changeContext->set($performedByActorId, $performedByActorRole, $sourceChannel, 'supplier_invoice_updated');

            $result = $this->operation->execute(
                $current,
                $supplierInvoiceId,
                $nomorFaktur,
                $namaPtPengirim,
                $tanggalPengiriman,
                $lines,
            );

            if ($result->isFailure()) {
                $this->transactions->rollBack();
                return $result;
            }

            $this->transactions->commit();

            return $result;
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        } finally {
            $this->changeContext->clear();
        }
    }
}
