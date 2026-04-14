<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use DateTimeImmutable;

final class OperationalExpenseReportSummaryBuilder
{
    public function build(
        array $rows,
        array $categoryRows,
        string $fromExpenseDate,
        string $toExpenseDate,
    ): array {
        $topCategory = $categoryRows[0] ?? null;
        $totalAmountRupiah = array_sum(array_column($rows, 'amount_rupiah'));
        $totalCalendarDays = $this->inclusiveCalendarDays($fromExpenseDate, $toExpenseDate);

        return [
            'total_rows' => count($rows),
            'total_amount_rupiah' => $totalAmountRupiah,
            'top_category_name' => $topCategory['category_name'] ?? null,
            'top_category_amount_rupiah' => $topCategory['total_amount_rupiah'] ?? 0,
            'average_daily_rupiah' => intdiv($totalAmountRupiah, $totalCalendarDays),
        ];
    }

    private function inclusiveCalendarDays(string $fromExpenseDate, string $toExpenseDate): int
    {
        $from = new DateTimeImmutable($fromExpenseDate);
        $to = new DateTimeImmutable($toExpenseDate);

        $days = (int) $from->diff($to)->days + 1;

        return $days > 0 ? $days : 1;
    }
}
