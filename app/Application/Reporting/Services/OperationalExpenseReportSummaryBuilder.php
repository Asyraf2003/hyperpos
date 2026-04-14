<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class OperationalExpenseReportSummaryBuilder
{
    public function build(array $rows, array $categoryRows): array
    {
        $topCategory = $categoryRows[0] ?? null;

        return [
            'total_rows' => count($rows),
            'total_amount_rupiah' => array_sum(array_column($rows, 'amount_rupiah')),
            'top_category_name' => $topCategory['category_name'] ?? null,
            'top_category_amount_rupiah' => $topCategory['total_amount_rupiah'] ?? 0,
            'average_daily_rupiah' => null,
        ];
    }
}
