<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use DateTimeImmutable;

final class SupplierInvoiceRevisionDeltaMovementsBuilder
{
    public function __construct(
        private readonly SupplierInvoiceRevisionMovementFactory $movements,
        private readonly SupplierInvoiceRevisionLineMapFactory $lineMaps,
        private readonly SupplierInvoiceRevisionPairedLineDeltaResolver $pairedDeltaResolver,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $requestLines
     * @return list<InventoryMovement>
     */
    public function build(
        SupplierInvoice $current,
        SupplierInvoice $updated,
        array $requestLines,
        DateTimeImmutable $movementDate,
    ): array {
        $oldLinesById = $this->lineMaps->oldLinesById($current);
        $newLinesByLineNo = $this->lineMaps->newLinesByLineNo($updated);
        $referencedOldIds = [];
        $deltaMovements = [];

        foreach ($requestLines as $requestLine) {
            if (! is_array($requestLine)) {
                continue;
            }

            $newLine = $newLinesByLineNo[(int) ($requestLine['line_no'] ?? 0)] ?? null;
            if ($newLine === null) {
                continue;
            }

            $previousLineId = $this->resolvePreviousLineId(
                $requestLine,
                $newLine,
                $oldLinesById,
                $referencedOldIds,
            );

            if ($previousLineId !== null) {
                $referencedOldIds[$previousLineId] = true;

                array_push(
                    $deltaMovements,
                    ...$this->pairedDeltaResolver->resolve(
                        $oldLinesById[$previousLineId],
                        $newLine,
                        $movementDate,
                    )
                );

                continue;
            }

            $deltaMovements[] = $this->movements->stockIn($newLine, $movementDate, $newLine->qtyPcs());
        }

        foreach ($oldLinesById as $oldLineId => $oldLine) {
            if (! isset($referencedOldIds[$oldLineId])) {
                $deltaMovements[] = $this->movements->stockOut($oldLine, $movementDate, $oldLine->qtyPcs());
            }
        }

        return $deltaMovements;
    }

    /**
     * @param array<string, mixed> $requestLine
     * @param array<string, SupplierInvoiceLine> $oldLinesById
     * @param array<string, bool> $referencedOldIds
     */
    private function resolvePreviousLineId(
        array $requestLine,
        SupplierInvoiceLine $newLine,
        array $oldLinesById,
        array $referencedOldIds,
    ): ?string {
        $previousLineId = $this->previousLineId($requestLine);

        if ($previousLineId !== '' && isset($oldLinesById[$previousLineId]) && ! isset($referencedOldIds[$previousLineId])) {
            return $previousLineId;
        }

        return $this->fallbackPreviousLineId($newLine, $oldLinesById, $referencedOldIds);
    }

    /**
     * @param array<string, SupplierInvoiceLine> $oldLinesById
     * @param array<string, bool> $referencedOldIds
     */
    private function fallbackPreviousLineId(
        SupplierInvoiceLine $newLine,
        array $oldLinesById,
        array $referencedOldIds,
    ): ?string {
        $matchedOldLineId = null;

        foreach ($oldLinesById as $oldLineId => $oldLine) {
            if (isset($referencedOldIds[$oldLineId])) {
                continue;
            }

            if (! $this->isSafeFallbackPair($oldLine, $newLine)) {
                continue;
            }

            if ($matchedOldLineId !== null) {
                return null;
            }

            $matchedOldLineId = $oldLineId;
        }

        return $matchedOldLineId;
    }

    private function isSafeFallbackPair(SupplierInvoiceLine $oldLine, SupplierInvoiceLine $newLine): bool
    {
        return $oldLine->lineNo() === $newLine->lineNo()
            && $oldLine->productId() === $newLine->productId()
            && $oldLine->qtyPcs() === $newLine->qtyPcs();
    }

    /**
     * @param array<string, mixed> $requestLine
     */
    private function previousLineId(array $requestLine): string
    {
        $value = $requestLine['previous_line_id'] ?? null;

        return is_string($value) ? trim($value) : '';
    }
}
