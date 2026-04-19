<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Inventory\Services\InventoryProjectionService;
use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Procurement\Services\SupplierReceiptFactory;
use App\Application\Procurement\Services\VoidedSupplierInvoiceGuard;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Procurement\SupplierInvoiceLineReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierReceiptWriterPort;
use App\Ports\Out\TransactionManagerPort;
use DateTimeImmutable;
use Throwable;

final class ReceiveSupplierInvoiceHandler
{
    public function __construct(
        private SupplierInvoiceReaderPort $invoices,
        private SupplierInvoiceLineReaderPort $invoiceLines,
        private SupplierReceiptWriterPort $receiptWriter,
        private InventoryMovementWriterPort $movementWriter,
        private TransactionManagerPort $transactions,
        private SupplierReceiptFactory $factory,
        private InventoryProjectionService $projection,
        private AuditLogPort $audit,
        private VoidedSupplierInvoiceGuard $voidedGuard,
        private SupplierInvoiceListProjectionService $invoiceProjection,
    ) {}

    public function handle(string $invoiceId, string $tanggal, array $lines): Result
    {
        $voided = $this->voidedGuard->ensureNotVoided($invoiceId);

        if ($voided->isFailure()) {
            return $voided;
        }

        $started = false;
        try {
            $this->transactions->begin();
            $started = true;

            $inv = $this->invoices->getById(trim($invoiceId)) ?? throw new DomainException('Invoice tidak ditemukan.');
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggal)) ?: throw new DomainException('Tanggal tidak valid.');

            [$receipt, $movements] = $this->factory->build(
                $inv->id(),
                $date,
                $lines,
                $this->indexLines($this->invoiceLines->getBySupplierInvoiceId($inv->id()))
            );

            $this->receiptWriter->create($receipt);
            $this->movementWriter->createMany($movements);
            $this->projection->applyMovements($movements);

            $this->audit->record('supplier_receipt_created', [
                'receipt_id' => $receipt->id(),
                'invoice_id' => $inv->id(),
                'line_count' => count($receipt->lines()),
            ]);

            $this->invoiceProjection->syncInvoice($inv->id());

            $this->transactions->commit();

            return Result::success(['id' => $receipt->id()], 'Supplier receipt berhasil dibuat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['receipt' => ['INVALID_SUPPLIER_RECEIPT']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function indexLines(array $lines): array
    {
        $indexed = [];

        foreach ($lines as $l) {
            $indexed[(string) $l['id']] = $l;
        }

        return $indexed;
    }
}
