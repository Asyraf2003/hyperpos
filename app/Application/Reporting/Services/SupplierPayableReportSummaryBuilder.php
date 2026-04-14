<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class SupplierPayableReportSummaryBuilder
{
    public function build(array $rows): array
    {
        $openRows = 0;
        $settledRows = 0;

        foreach ($rows as $row) {
            $outstanding = (int) ($row['outstanding_rupiah'] ?? 0);

            if ($outstanding > 0) {
                $openRows++;
                continue;
            }

            $settledRows++;
        }

        return [
            'total_rows' => count($rows),
            'grand_total_rupiah' => array_sum(array_column($rows, 'grand_total_rupiah')),
            'total_paid_rupiah' => array_sum(array_column($rows, 'total_paid_rupiah')),
            'outstanding_rupiah' => array_sum(array_column($rows, 'outstanding_rupiah')),
            'receipt_count' => array_sum(array_column($rows, 'receipt_count')),
            'total_received_qty' => array_sum(array_column($rows, 'total_received_qty')),
            'open_rows' => $openRows,
            'settled_rows' => $settledRows,
        ];
    }
}
