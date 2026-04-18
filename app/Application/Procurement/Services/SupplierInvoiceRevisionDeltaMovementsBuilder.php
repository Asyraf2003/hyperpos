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

            $previousLineId = $this->previousLineId($requestLine);
            if ($previousLineId !== '' && isset($oldLinesById[$previousLineId])) {
                $referencedOldIds[$previousLineId] = true;

                array_push(
                    $deltaMovements,
                    ...$this->pairedLineDeltaMovements($oldLinesById[$previousLineId], $newLine, $movementDate)
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
     * @return list<InventoryMovement>
     */
    private function pairedLineDeltaMovements(
        SupplierInvoiceLine $oldLine,
        SupplierInvoiceLine $newLine,
        DateTimeImmutable $movementDate,
    ): array {
        if ($oldLine->productId() !== $newLine->productId()) {
            return [
                $this->movements->stockOut($oldLine, $movementDate, $oldLine->qtyPcs()),
                $this->movements->stockIn($newLine, $movementDate, $newLine->qtyPcs()),
            ];
        }

        $deltaQty = $newLine->qtyPcs() - $oldLine->qtyPcs();

        return match (true) {
            $deltaQty > 0 => [$this->movements->stockIn($newLine, $movementDate, $deltaQty)],
            $deltaQty < 0 => [$this->movements->stockOut($oldLine, $movementDate, abs($deltaQty))],
            default => [],
        };
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
