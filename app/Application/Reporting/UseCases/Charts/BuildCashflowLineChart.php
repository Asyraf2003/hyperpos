<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

final class BuildCashflowLineChart
{
    public function __construct(
        private readonly CashflowLineChartPeriodsFactory $periodsFactory,
        private readonly CashflowLineChartDatasetBuilder $datasetBuilder,
    ) {
    }

    /**
     * @param list<array{
     *   note_id:string,
     *   event_date:string,
     *   event_type:string,
     *   direction:string,
     *   event_amount_rupiah:int,
     *   customer_payment_id:?string,
     *   refund_id:?string
     * }> $rows
     * @return array<string, mixed>
     */
    public function build(array $rows, string $fromDate, string $toDate): array
    {
        $periods = $this->periodsFactory->create($fromDate, $toDate);

        foreach ($rows as $row) {
            $date = $row['event_date'];
            $direction = $row['direction'];
            $amount = $row['event_amount_rupiah'];

            if (! isset($periods[$date])) {
                continue;
            }

            if ($direction === 'in') {
                $periods[$date]['cash_in'] += $amount;
                continue;
            }

            if ($direction === 'out') {
                $periods[$date]['cash_out'] += $amount;
            }
        }

        return $this->datasetBuilder->build($periods, $fromDate, $toDate);
    }
}
