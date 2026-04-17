<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

final class GetDashboardOperationalPerformanceDatasetHandler
{
    public function __construct(
        private readonly DashboardOperationalPerformanceReaderPort $sourceReader,
    ) {
    }

    public function handle(string $fromDate, string $toDate): array
    {
        $rows = $this->sourceReader->getOperationalPerformancePeriodRows(
            $fromDate,
            $toDate,
        );

        return [
            'period_rows' => array_map(
                static fn (array $row): array => [
                    'period_key' => (string) ($row['period_key'] ?? ''),
                    'period_label' => (string) ($row['period_label'] ?? ''),
                    'operational_profit_rupiah' => (int) ($row['operational_profit_rupiah'] ?? 0),
                    'operational_expense_rupiah' => (int) ($row['operational_expense_rupiah'] ?? 0),
                    'refund_rupiah' => (int) ($row['refund_rupiah'] ?? 0),
                ],
                $rows,
            ),
            'summary' => [
                'total_operational_profit_rupiah' => $this->sum($rows, 'operational_profit_rupiah'),
                'total_operational_expense_rupiah' => $this->sum($rows, 'operational_expense_rupiah'),
                'total_refund_rupiah' => $this->sum($rows, 'refund_rupiah'),
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function sum(array $rows, string $field): int
    {
        $total = 0;

        foreach ($rows as $row) {
            $total += (int) ($row[$field] ?? 0);
        }

        return $total;
    }
}
