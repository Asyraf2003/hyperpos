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
        $oldLinesById = $this->oldLinesById($current);
        $newLinesByLineNo = $this->newLinesByLineNo($updated);
        $referencedOldIds = [];
        $deltaMovements = [];

        foreach ($requestLines as $requestLine) {
            if (! is_array($requestLine)) {
                continue;
            }

            $previousLineId = $this->previousLineId($requestLine);
            $lineNo = isset($requestLine['line_no']) ? (int) $requestLine['line_no'] : 0;
            $newLine = $newLinesByLineNo[$lineNo] ?? null;

            if ($newLine === null) {
                continue;
            }

            if ($previousLineId !== '' && isset($oldLinesById[$previousLineId])) {
                $referencedOldIds[$previousLineId] = true;
                array_push($deltaMovements, ...$this->pairedLineDeltaMovements($oldLinesById[$previousLineId], $newLine, $movementDate));
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
     * @return array<string, SupplierInvoiceLine>
     */
    private function oldLinesById(SupplierInvoice $invoice): array
    {
        $lines = [];

        foreach ($invoice->lines() as $line) {
            $lines[$line->id()] = $line;
        }

        return $lines;
    }

    /**
     * @return array<int, SupplierInvoiceLine>
     */
    private function newLinesByLineNo(SupplierInvoice $invoice): array
    {
        $lines = [];

        foreach ($invoice->lines() as $line) {
            $lines[$line->lineNo()] = $line;
        }

        return $lines;
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
