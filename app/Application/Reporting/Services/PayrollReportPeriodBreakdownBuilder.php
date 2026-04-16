<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class PayrollReportPeriodBreakdownBuilder
{
    public function build(array $rows): array
    {
        $periods = [];

        foreach ($rows as $row) {
            $date = (string) ($row['disbursement_date'] ?? '');

            if ($date === '') {
                continue;
            }

            $periods[$date] ??= [
                'period_label' => $date,
                'total_rows' => 0,
                'total_amount_rupiah' => 0,
            ];

            $periods[$date]['total_rows']++;
            $periods[$date]['total_amount_rupiah'] += (int) ($row['amount_rupiah'] ?? 0);
        }

        ksort($periods);

        return array_values($periods);
    }
}
