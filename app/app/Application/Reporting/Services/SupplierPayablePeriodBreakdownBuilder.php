<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class SupplierPayablePeriodBreakdownBuilder
{
    public function build(array $rows): array
    {
        $periods = [];

        foreach ($rows as $row) {
            $shipmentDate = (string) ($row['shipment_date'] ?? '');

            if ($shipmentDate === '') {
                continue;
            }

            if (! isset($periods[$shipmentDate])) {
                $periods[$shipmentDate] = [
                    'period_label' => $shipmentDate,
                    'total_rows' => 0,
                    'grand_total_rupiah' => 0,
                    'total_paid_rupiah' => 0,
                    'outstanding_rupiah' => 0,
                ];
            }

            $periods[$shipmentDate]['total_rows']++;
            $periods[$shipmentDate]['grand_total_rupiah'] += (int) ($row['grand_total_rupiah'] ?? 0);
            $periods[$shipmentDate]['total_paid_rupiah'] += (int) ($row['total_paid_rupiah'] ?? 0);
            $periods[$shipmentDate]['outstanding_rupiah'] += (int) ($row['outstanding_rupiah'] ?? 0);
        }

        ksort($periods);

        return array_values($periods);
    }
}
