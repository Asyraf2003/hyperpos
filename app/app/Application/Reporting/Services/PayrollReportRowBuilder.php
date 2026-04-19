<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\PayrollReportRow;

final class PayrollReportRowBuilder
{
    public function build(array $rawRows): array
    {
        return array_map(function (array $row): PayrollReportRow {
            $modeValue = (string) ($row['mode_value'] ?? '');

            return new PayrollReportRow(
                (string) ($row['id'] ?? ''),
                (string) ($row['employee_id'] ?? ''),
                (string) ($row['employee_name'] ?? ''),
                (int) ($row['amount_rupiah'] ?? 0),
                (string) ($row['disbursement_date'] ?? ''),
                $modeValue,
                $this->modeLabel($modeValue),
                isset($row['notes']) ? (string) $row['notes'] : null,
            );
        }, $rawRows);
    }

    private function modeLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($value),
        };
    }
}
