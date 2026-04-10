<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\SupplierInvoiceFactory;
use App\Application\Procurement\Services\SupplierService;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use DateTimeImmutable;
use Throwable;

final class UpdateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $reader,
        private readonly SupplierInvoiceWriterPort $writer,
        private readonly SupplierService $supplierService,
        private readonly SupplierInvoiceFactory $invoiceFactory,
        private readonly SupplierInvoiceEditabilityGuard $guard,
        private readonly TransactionManagerPort $transactions,
        private readonly SupplierInvoiceChangeContext $changeContext,
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
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        $guard = $this->guard->ensureEditable($supplierInvoiceId);
        if ($guard->isFailure()) {
            return $guard;
        }

        $started = false;

        try {
            $shipmentDate = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggalPengiriman))
                ?: throw new DomainException('Format tanggal pengiriman salah.');

            $invoiceLines = $this->invoiceFactory->makeLines($lines);

            $this->transactions->begin();
            $started = true;

            $supplier = $this->supplierService->resolve($namaPtPengirim);

            $this->changeContext->set(
                $performedByActorId,
                $performedByActorRole,
                $sourceChannel,
                'supplier_invoice_updated',
            );

            $updated = SupplierInvoice::create(
                $current->id(),
                $supplier->id(),
                $supplier->namaPtPengirim(),
                trim($nomorFaktur),
                $shipmentDate,
                $invoiceLines,
            );

            $this->writer->update($updated);
            $this->transactions->commit();

            return Result::success(
                ['id' => $updated->id()],
                'Nota supplier berhasil diperbarui.'
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]
            );
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
