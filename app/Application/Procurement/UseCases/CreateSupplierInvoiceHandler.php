<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Application\Procurement\Services\SupplierInvoiceFactory;
use App\Application\Procurement\Services\SupplierService;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class CreateSupplierInvoiceHandler
{
    public function __construct(
        private SupplierInvoiceWriterPort $writer,
        private TransactionManagerPort $transactions,
        private UuidPort $uuid,
        private SupplierService $supplierService,
        private SupplierInvoiceFactory $invoiceFactory,
        private SupplierInvoiceChangeContext $changeContext,
    ) {
    }

    public function handle(
        string $nomorFaktur,
        string $pt,
        string $tgl,
        array $lines,
        ?string $performedByActorId = null,
        ?string $performedByActorRole = null,
        string $sourceChannel = 'http',
    ): Result {
        $started = false;

        try {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tgl))
                ?: throw new DomainException('Format tanggal salah.');

            $invoiceLines = $this->invoiceFactory->makeLines($lines);

            $this->transactions->begin();
            $started = true;

            $supplier = $this->supplierService->resolve($pt);

            $this->changeContext->set(
                $performedByActorId,
                $performedByActorRole,
                $sourceChannel,
                'supplier_invoice_created',
            );

            $invoice = SupplierInvoice::create(
                $this->uuid->generate(),
                $supplier->id(),
                $supplier->namaPtPengirim(),
                trim($nomorFaktur),
                $date,
                $invoiceLines
            );

            $this->writer->create($invoice);

            $this->transactions->commit();

            return Result::success(
                ['id' => $invoice->id()],
                'Supplier invoice berhasil dibuat.'
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
