<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\SupplierPayableSummaryRow;

final class SupplierPayableSummaryBuilder
{
    /**
     * @param list<array{
     *   supplier_invoice_id:string,
     *   supplier_id:string,
     *   shipment_date:string,
     *   due_date:string,
     *   grand_total_rupiah:int,
     *   total_paid_rupiah:int,
     *   receipt_count:int,
     *   total_received_qty:int
     * }> $rows
     * @return list<SupplierPayableSummaryRow>
     */
    public function build(array $rows): array
    {
        return array_map(
            static function (array $row): SupplierPayableSummaryRow {
                return new SupplierPayableSummaryRow(
                    $row['supplier_invoice_id'],
                    $row['supplier_id'],
                    $row['shipmentDate'] ?? $row['shipment_date'],
                    $row['dueDate'] ?? $row['due_date'],
                    $row['grand_total_rupiah'],
                    $row['total_paid_rupiah'],
                    $row['grand_total_rupiah'] - $row['total_paid_rupiah'],
                    $row['receipt_count'],
                    $row['total_received_qty'],
                );
            },
            $rows,
        );
    }
}
