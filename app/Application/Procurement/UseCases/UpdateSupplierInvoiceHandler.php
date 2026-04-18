<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\UpdatedSupplierInvoiceBuilder;
use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class UpdateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $reader,
        private readonly SupplierInvoiceWriterPort $writer,
        private readonly UpdatedSupplierInvoiceBuilder $builder,
        private readonly SupplierInvoiceEditabilityGuard $guard,
        private readonly TransactionManagerPort $transactions,
        private readonly SupplierInvoiceChangeContext $changeContext,
        private readonly GetProcurementInvoiceDetailHandler $details,
        private readonly InventoryMovementWriterPort $inventoryMovements,
        private readonly UuidPort $uuid,
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
            $this->transactions->begin();
            $started = true;

            $this->changeContext->set(
                $performedByActorId,
                $performedByActorRole,
                $sourceChannel,
                'supplier_invoice_updated',
            );

            $updated = $this->builder->build(
                $current,
                $nomorFaktur,
                $namaPtPengirim,
                $tanggalPengiriman,
                $lines,
            );

            $summary = $this->resolveSummary($supplierInvoiceId);
            $totalPaidRupiah = (int) ($summary['total_paid_rupiah'] ?? 0);
            $totalReceivedQty = (int) ($summary['total_received_qty'] ?? 0);

            if ($updated->grandTotalRupiah()->amount() < $totalPaidRupiah) {
                $this->transactions->rollBack();
                $started = false;

                return Result::failure(
                    'Total revisi tidak boleh lebih kecil dari total pembayaran yang sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISED_TOTAL_BELOW_TOTAL_PAID']]
                );
            }

            $this->writer->update($updated);

            if ($totalReceivedQty > 0) {
                $deltaMovements = $this->buildRevisionDeltaMovements($current, $updated, $lines);

                if ($deltaMovements !== []) {
                    $this->inventoryMovements->createMany($deltaMovements);
                }
            }

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

    /**
     * @return array<string, mixed>
     */
    private function resolveSummary(string $supplierInvoiceId): array
    {
        $detail = $this->details->handle($supplierInvoiceId);
        $payload = $detail->data();

        if (! is_array($payload)) {
            return [];
        }

        return is_array($payload['summary'] ?? null)
            ? $payload['summary']
            : [];
    }

    /**
     * @param list<array<string, mixed>> $requestLines
     * @return list<InventoryMovement>
     */
    private function buildRevisionDeltaMovements(
        SupplierInvoice $current,
        SupplierInvoice $updated,
        array $requestLines,
    ): array {
        $oldLinesById = [];
        foreach ($current->lines() as $line) {
            $oldLinesById[$line->id()] = $line;
        }

        $newLinesByLineNo = [];
        foreach ($updated->lines() as $line) {
            $newLinesByLineNo[$line->lineNo()] = $line;
        }

        $referencedOldIds = [];
        $movements = [];

        foreach ($requestLines as $requestLine) {
            if (! is_array($requestLine)) {
                continue;
            }

            $previousLineId = isset($requestLine['previous_line_id']) && is_string($requestLine['previous_line_id'])
                ? trim($requestLine['previous_line_id'])
                : '';

            $lineNo = isset($requestLine['line_no']) ? (int) $requestLine['line_no'] : 0;
            $newLine = $newLinesByLineNo[$lineNo] ?? null;

            if ($newLine === null) {
                continue;
            }

            if ($previousLineId !== '' && isset($oldLinesById[$previousLineId])) {
                $referencedOldIds[$previousLineId] = true;
                $oldLine = $oldLinesById[$previousLineId];

                array_push(
                    $movements,
                    ...$this->buildPairedLineDeltaMovements($oldLine, $newLine, $updated->tanggalPengiriman())
                );

                continue;
            }

            $movements[] = $this->makeStockInMovement(
                $newLine,
                $updated->tanggalPengiriman(),
                $newLine->qtyPcs()
            );
        }

        foreach ($oldLinesById as $oldLineId => $oldLine) {
            if (isset($referencedOldIds[$oldLineId])) {
                continue;
            }

            $movements[] = $this->makeStockOutMovement(
                $oldLine,
                $updated->tanggalPengiriman(),
                $oldLine->qtyPcs()
            );
        }

        return $movements;
    }

    /**
     * @return list<InventoryMovement>
     */
    private function buildPairedLineDeltaMovements(
        SupplierInvoiceLine $oldLine,
        SupplierInvoiceLine $newLine,
        \DateTimeImmutable $movementDate,
    ): array {
        if ($oldLine->productId() !== $newLine->productId()) {
            return [
                $this->makeStockOutMovement($oldLine, $movementDate, $oldLine->qtyPcs()),
                $this->makeStockInMovement($newLine, $movementDate, $newLine->qtyPcs()),
            ];
        }

        $deltaQty = $newLine->qtyPcs() - $oldLine->qtyPcs();

        if ($deltaQty > 0) {
            return [
                $this->makeStockInMovement($newLine, $movementDate, $deltaQty),
            ];
        }

        if ($deltaQty < 0) {
            return [
                $this->makeStockOutMovement($oldLine, $movementDate, abs($deltaQty)),
            ];
        }

        return [];
    }

    private function makeStockInMovement(
        SupplierInvoiceLine $line,
        \DateTimeImmutable $movementDate,
        int $qty,
    ): InventoryMovement {
        return InventoryMovement::create(
            $this->uuid->generate(),
            $line->productId(),
            'stock_in',
            'supplier_invoice_revision_delta_line',
            $line->id(),
            $movementDate,
            $qty,
            $line->unitCostRupiah(),
        );
    }

    private function makeStockOutMovement(
        SupplierInvoiceLine $line,
        \DateTimeImmutable $movementDate,
        int $qty,
    ): InventoryMovement {
        return InventoryMovement::create(
            $this->uuid->generate(),
            $line->productId(),
            'stock_out',
            'supplier_invoice_revision_delta_line',
            $line->id(),
            $movementDate,
            -$qty,
            $line->unitCostRupiah(),
        );
    }
}
