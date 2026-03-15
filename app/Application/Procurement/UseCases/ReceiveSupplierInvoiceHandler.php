<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Inventory\Services\InventoryProjectionService;
use App\Application\Procurement\Services\SupplierReceiptFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\{SupplierInvoiceReaderPort, SupplierInvoiceLineReaderPort, SupplierReceiptWriterPort};
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
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
        private InventoryProjectionService $projection
    ) {}

    public function handle(string $invoiceId, string $tanggal, array $lines): Result
    {
        $started = false;
        try {
            $this->transactions->begin(); $started = true;

            $inv = $this->invoices->getById(trim($invoiceId)) ?? throw new DomainException('Invoice tidak ditemukan.');
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggal)) ?: throw new DomainException('Tanggal tidak valid.');
            
            $indexedLines = $this->indexLines($this->invoiceLines->getBySupplierInvoiceId($inv->id()));
            
            [$receipt, $movements] = $this->factory->build($inv->id(), $date, $lines, $indexedLines);

            $this->receiptWriter->create($receipt);
            $this->movementWriter->createMany($movements);
            $this->projection->applyMovements($movements);

            $this->transactions->commit();
            return Result::success(['id' => $receipt->id()], 'Supplier receipt berhasil dibuat.');

        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return Result::failure($e->getMessage(), ['receipt' => ['INVALID_SUPPLIER_RECEIPT']]);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }

    private function indexLines(array $lines): array
    {
        $indexed = [];
        foreach ($lines as $l) $indexed[(string)$l['id']] = $l;
        return $indexed;
    }
}
