<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class TransactionCashLedgerPeriodTableBuilder
{
    public function build(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $date = (string) ($row['event_date'] ?? '');

            if ($date === '') {
                continue;
            }

            if (! isset($groups[$date])) {
                $groups[$date] = [
                    'period_label' => $date,
                    'total_events' => 0,
                    'cash_in_rupiah' => 0,
                    'cash_out_rupiah' => 0,
                    'net_amount_rupiah' => 0,
                ];
            }

            $amount = (int) ($row['event_amount_rupiah'] ?? 0);
            $groups[$date]['total_events']++;

            if (($row['direction'] ?? null) === 'in') {
                $groups[$date]['cash_in_rupiah'] += $amount;
            }

            if (($row['direction'] ?? null) === 'out') {
                $groups[$date]['cash_out_rupiah'] += $amount;
            }

            $groups[$date]['net_amount_rupiah'] =
                $groups[$date]['cash_in_rupiah'] - $groups[$date]['cash_out_rupiah'];
        }

        ksort($groups);

        return array_values($groups);
    }
}
