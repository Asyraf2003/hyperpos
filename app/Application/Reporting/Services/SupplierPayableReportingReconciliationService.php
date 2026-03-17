<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\SupplierPayableSummaryRow;

final class SupplierPayableReportingReconciliationService
{
    /**
     * @param list<SupplierPayableSummaryRow> $rows
     * @param array{
     *   total_rows:int,
     *   grand_total_rupiah:int,
     *   total_paid_rupiah:int,
     *   outstanding_rupiah:int
     * } $expected
     */
    public function assertSupplierPayableSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalRows = count($rows);
        $actualGrandTotal = 0;
        $actualTotalPaid = 0;
        $actualOutstanding = 0;

        foreach ($rows as $row) {
            $actualGrandTotal += $row->grandTotalRupiah();
            $actualTotalPaid += $row->totalPaidRupiah();
            $actualOutstanding += $row->outstandingRupiah();
        }

        if ($actualTotalRows !== $expected['total_rows']) {
            throw new \RuntimeException('Reporting mismatch: supplier_payable_total_rows.');
        }

        if ($actualGrandTotal !== $expected['grand_total_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: supplier_payable_grand_total_rupiah.');
        }

        if ($actualTotalPaid !== $expected['total_paid_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: supplier_payable_total_paid_rupiah.');
        }

        if ($actualOutstanding !== $expected['outstanding_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: supplier_payable_outstanding_rupiah.');
        }
    }
}
