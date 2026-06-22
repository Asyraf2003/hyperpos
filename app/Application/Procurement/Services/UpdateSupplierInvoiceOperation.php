<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;

final class UpdateSupplierInvoiceOperation
{
    public function __construct(
        private readonly SupplierInvoiceWriterPort $writer,
        private readonly UpdatedSupplierInvoiceBuilder $builder,
        private readonly SupplierInvoiceRevisionContextResolver $contextResolver,
        private readonly SupplierInvoiceRevisionDeltaMovementsBuilder $deltaMovements,
        private readonly SupplierInvoiceRevisionDeltaStockGuard $deltaStockGuard,
        private readonly SupplierInvoiceRevisionInventoryEffectsApplier $inventoryEffects,
    ) {
    }

    public function execute(
        SupplierInvoice $current,
        string $supplierInvoiceId,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
        null|string|int $taxInput = null,
    ): Result {
        $updated = $this->builder->build($current, $nomorFaktur, $namaPtPengirim, $tanggalPengiriman, $lines, $taxInput);
        $context = $this->contextResolver->resolve($supplierInvoiceId, $updated);

        if ($updated->grandTotalRupiah()->amount() < $context->totalPaidRupiah()) {
            return Result::failure(
                'Total revisi tidak boleh lebih kecil dari total pembayaran yang sudah tercatat.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISED_TOTAL_BELOW_TOTAL_PAID']]
            );
        }

        if ($context->totalReceivedQty() > 0 && $this->changesReceivedUnitCost($current, $updated, $lines)) {
            return Result::failure(
                'Revisi faktur yang mengubah modal/unit cost pada barang yang sudah diterima belum didukung. Buat koreksi stok/modal terpisah agar laporan keuntungan tetap presisi.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_RECEIVED_UNIT_COST_REVISION_UNSUPPORTED']]
            );
        }

        $deltaMovements = $context->totalReceivedQty() > 0
            ? $this->deltaMovements->build($current, $updated, $lines, $context->movementDate())
            : [];

        if (! $this->deltaStockGuard->canApplyWithoutNegativeStock($deltaMovements)) {
            return Result::failure(
                'Revisi faktur akan membuat stok product lama menjadi negatif.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISION_NEGATIVE_STOCK']]
            );
        }

        $this->writer->update($updated);

        $inventoryEffects = $this->inventoryEffects->apply($deltaMovements);
        if ($inventoryEffects->isFailure()) {
            return $inventoryEffects;
        }

        return Result::success(['id' => $updated->id()], 'Nota supplier berhasil diperbarui.');
    }
    /**
     * @param array<int, mixed> $submittedLines
     */
    private function changesReceivedUnitCost(SupplierInvoice $current, SupplierInvoice $updated, array $submittedLines): bool
    {
        $currentLinesById = [];

        foreach ($current->lines() as $currentLine) {
            $currentLinesById[$currentLine->id()] = $currentLine;
        }

        foreach ($updated->lines() as $index => $updatedLine) {
            $submittedLine = $submittedLines[$index] ?? null;

            if (! is_array($submittedLine)) {
                continue;
            }

            $previousLineId = trim((string) ($submittedLine['previous_line_id'] ?? ''));

            if ($previousLineId === '' || ! array_key_exists($previousLineId, $currentLinesById)) {
                continue;
            }

            $currentLine = $currentLinesById[$previousLineId];

            if ($currentLine->productId() !== $updatedLine->productId()) {
                continue;
            }

            if ($currentLine->unitCostRupiah()->amount() !== $updatedLine->unitCostRupiah()->amount()) {
                return true;
            }
        }

        return false;
    }

}
