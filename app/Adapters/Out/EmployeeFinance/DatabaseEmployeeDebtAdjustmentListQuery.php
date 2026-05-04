<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\EmployeeDebtAdjustmentListReaderPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtAdjustmentListQuery implements EmployeeDebtAdjustmentListReaderPort
{
    public function findByDebtId(string $debtId): array
    {
        return DB::table('employee_debt_adjustments')
            ->select(['adjustment_type', 'amount', 'reason', 'created_at'])
            ->where('employee_debt_id', $debtId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (object $row): array {
                $type = (string) $row->adjustment_type;

                return [
                    'adjustment_type' => $type,
                    'adjustment_type_label' => $type === 'increase'
                        ? 'Tambah Hutang'
                        : 'Pengurangan Hutang (data lama)',
                    'amount_formatted' => number_format((int) $row->amount, 0, ',', '.'),
                    'reason' => (string) $row->reason,
                    'recorded_at' => Carbon::parse((string) $row->created_at)->format('Y-m-d H:i'),
                ];
            })
            ->values()
            ->all();
    }
}
