<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use DateTimeImmutable;

final class PayrollReportSummaryBuilder
{
    public function build(array $rows, array $modeRows, string $fromDate, string $toDate): array
    {
        $totalAmount = array_sum(array_column($rows, 'amount_rupiah'));
        $latestDate = $rows === [] ? null : max(array_column($rows, 'disbursement_date'));
        $topMode = $modeRows[0] ?? null;
        $days = max(1, (int) (new DateTimeImmutable($fromDate))->diff(new DateTimeImmutable($toDate))->days + 1);

        return [
            'total_rows' => count($rows),
            'total_amount_rupiah' => $totalAmount,
            'latest_disbursement_date' => $latestDate,
            'top_mode_label' => $topMode['mode_label'] ?? null,
            'top_mode_amount_rupiah' => $topMode['total_amount_rupiah'] ?? 0,
            'average_daily_rupiah' => intdiv($totalAmount, $days),
        ];
    }
}
