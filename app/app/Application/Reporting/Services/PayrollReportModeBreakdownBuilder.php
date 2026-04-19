<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class PayrollReportModeBreakdownBuilder
{
    public function build(array $rows): array
    {
        $modes = [];

        foreach ($rows as $row) {
            $mode = (string) ($row['mode_value'] ?? '');

            if ($mode === '') {
                continue;
            }

            $modes[$mode] ??= [
                'mode_value' => $mode,
                'mode_label' => (string) ($row['mode_label'] ?? ucfirst($mode)),
                'total_rows' => 0,
                'total_amount_rupiah' => 0,
            ];

            $modes[$mode]['total_rows']++;
            $modes[$mode]['total_amount_rupiah'] += (int) ($row['amount_rupiah'] ?? 0);
        }

        $modeRows = array_values($modes);

        usort($modeRows, static function (array $left, array $right): int {
            $byAmount = $right['total_amount_rupiah'] <=> $left['total_amount_rupiah'];
            return $byAmount !== 0 ? $byAmount : strcmp($left['mode_label'], $right['mode_label']);
        });

        return $modeRows;
    }
}
