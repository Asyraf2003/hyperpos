<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class SupplierPayableSupplierBreakdownBuilder
{
    public function build(array $rows): array
    {
        $suppliers = [];

        foreach ($rows as $row) {
            $supplierId = (string) ($row['supplier_id'] ?? '');

            if ($supplierId === '') {
                continue;
            }

            if (! isset($suppliers[$supplierId])) {
                $suppliers[$supplierId] = [
                    'supplier_id' => $supplierId,
                    'total_rows' => 0,
                    'grand_total_rupiah' => 0,
                    'total_paid_rupiah' => 0,
                    'outstanding_rupiah' => 0,
                ];
            }

            $suppliers[$supplierId]['total_rows']++;
            $suppliers[$supplierId]['grand_total_rupiah'] += (int) ($row['grand_total_rupiah'] ?? 0);
            $suppliers[$supplierId]['total_paid_rupiah'] += (int) ($row['total_paid_rupiah'] ?? 0);
            $suppliers[$supplierId]['outstanding_rupiah'] += (int) ($row['outstanding_rupiah'] ?? 0);
        }

        $supplierRows = array_values($suppliers);

        usort($supplierRows, static function (array $left, array $right): int {
            $byOutstanding = $right['outstanding_rupiah'] <=> $left['outstanding_rupiah'];

            if ($byOutstanding !== 0) {
                return $byOutstanding;
            }

            return strcmp((string) $left['supplier_id'], (string) $right['supplier_id']);
        });

        return $supplierRows;
    }
}
