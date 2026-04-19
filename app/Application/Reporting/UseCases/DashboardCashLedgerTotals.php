<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class DashboardCashLedgerTotals
{
    public static function fromReportResult(object $result): array
    {
        return self::fromRows(ReportingResultDataExtractor::rows($result));
    }

    public static function fromRows(array $rows): array
    {
        $totalIn = 0;
        $totalOut = 0;

        foreach ($rows as $row) {
            $direction = (string) ($row['direction'] ?? '');
            $amount = (int) ($row['event_amount_rupiah'] ?? 0);

            if ($direction === 'in') {
                $totalIn += $amount;
                continue;
            }

            if ($direction === 'out') {
                $totalOut += $amount;
            }
        }

        return [
            'total_in_rupiah' => $totalIn,
            'total_out_rupiah' => $totalOut,
        ];
    }
}
