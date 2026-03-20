<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceAutoReceiveProcessor;
use App\Application\Procurement\Services\SupplierInvoiceFactory;
use App\Application\Procurement\Services\SupplierInvoiceFlowDateResolver;
use App\Application\Procurement\Services\SupplierService;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class CreateSupplierInvoiceFlowHandler
{
    public function __construct(
        private SupplierInvoiceWriterPort $invoiceWriter,
        private TransactionManagerPort $transactions,
        private UuidPort $uuid,
        private SupplierService $supplierService,
        private SupplierInvoiceFactory $invoiceFactory,
        private SupplierInvoiceFlowDateResolver $dateResolver,
        private SupplierInvoiceAutoReceiveProcessor $autoReceiveProcessor,
        private AuditLogPort $audit
    ) {
    }

    public function handle(
        string $pt,
        string $tglKirim,
        array $lines,
        bool $autoRec = true,
        ?string $tglTerima = null
    ): Result {
        $started = false;

        try {
            [$dateKirim, $dateTerima] = $this->dateResolver->resolve($tglKirim, $autoRec, $tglTerima);

            $this->transactions->begin();
            $started = true;

            $supplier = $this->supplierService->resolve($pt);
            $invoice = SupplierInvoice::create(
                $this->uuid->generate(),
                $supplier->id(),
                $supplier->namaPtPengirim(),
                $dateKirim,
                $this->invoiceFactory->makeLines($lines)
            );

            $this->invoiceWriter->create($invoice);

            if ($autoRec && $dateTerima !== null) {
                $this->autoReceiveProcessor->process($invoice, $dateTerima);
            }

            $this->audit->record('supplier_invoice_flow_completed', [
                'invoice_id' => $invoice->id(),
                'supplier_name' => $supplier->namaPtPengirim(),
                'grand_total' => $invoice->grandTotalRupiah()->amount(),
                'auto_received' => $autoRec,
            ]);

            $this->transactions->commit();

            return Result::success(['id' => $invoice->id()], 'Flow Supplier Invoice Berhasil.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return Result::failure($e->getMessage(), ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }
}
