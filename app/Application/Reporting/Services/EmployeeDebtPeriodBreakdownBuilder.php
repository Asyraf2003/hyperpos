<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class EmployeeDebtPeriodBreakdownBuilder
{
    public function build(array $rows): array
    {
        $periods = [];

        foreach ($rows as $row) {
            $recordedAt = (string) ($row['recorded_at'] ?? '');
            $date = substr($recordedAt, 0, 10);

            if ($date === '') {
                continue;
            }

            if (! isset($periods[$date])) {
                $periods[$date] = [
                    'period_label' => $date,
                    'total_rows' => 0,
                    'total_debt' => 0,
                    'total_paid_amount' => 0,
                    'total_remaining_balance' => 0,
                ];
            }

            $periods[$date]['total_rows']++;
            $periods[$date]['total_debt'] += (int) ($row['total_debt'] ?? 0);
            $periods[$date]['total_paid_amount'] += (int) ($row['total_paid_amount'] ?? 0);
            $periods[$date]['total_remaining_balance'] += (int) ($row['remaining_balance'] ?? 0);
        }

        ksort($periods);

        return array_values($periods);
    }
}
