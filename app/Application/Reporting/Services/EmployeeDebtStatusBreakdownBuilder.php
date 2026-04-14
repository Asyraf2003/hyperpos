<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class EmployeeDebtStatusBreakdownBuilder
{
    public function build(array $rows): array
    {
        $statuses = [];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');

            if ($status === '') {
                continue;
            }

            if (! isset($statuses[$status])) {
                $statuses[$status] = [
                    'status' => $status,
                    'total_rows' => 0,
                    'total_debt' => 0,
                    'total_paid_amount' => 0,
                    'total_remaining_balance' => 0,
                ];
            }

            $statuses[$status]['total_rows']++;
            $statuses[$status]['total_debt'] += (int) ($row['total_debt'] ?? 0);
            $statuses[$status]['total_paid_amount'] += (int) ($row['total_paid_amount'] ?? 0);
            $statuses[$status]['total_remaining_balance'] += (int) ($row['remaining_balance'] ?? 0);
        }

        $statusRows = array_values($statuses);

        usort($statusRows, static fn (array $left, array $right): int => strcmp(
            (string) $left['status'],
            (string) $right['status'],
        ));

        return $statusRows;
    }
}
