<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeePayrollTableQuery;
use App\Ports\Out\EmployeeFinance\EmployeePayrollTableReaderPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeePayrollTableReaderAdapter implements EmployeePayrollTableReaderPort
{
    public function search(string $employeeId, EmployeePayrollTableQuery $query): array
    {
        $paginator = DB::table('payroll_disbursements')
            ->leftJoin(
                'payroll_disbursement_reversals',
                'payroll_disbursements.id',
                '=',
                'payroll_disbursement_reversals.payroll_disbursement_id'
            )
            ->select([
                'payroll_disbursements.id',
                'payroll_disbursements.amount',
                'payroll_disbursements.disbursement_date',
                'payroll_disbursements.mode',
                'payroll_disbursements.notes',
                'payroll_disbursement_reversals.id as reversal_id',
                'payroll_disbursement_reversals.reason as reversal_reason',
                'payroll_disbursement_reversals.created_at as reversal_created_at',
            ])
            ->where('payroll_disbursements.employee_id', $employeeId)
            ->orderByDesc('payroll_disbursements.disbursement_date')
            ->orderByDesc('payroll_disbursements.created_at')
            ->paginate($query->perPage(), ['*'], 'page', $query->page());

        return [
            'rows' => collect($paginator->items())->map(function (object $row): array {
                $amount = (int) $row->amount;
                $modeValue = (string) $row->mode;
                $isReversed = $row->reversal_id !== null;

                return [
                    'id' => (string) $row->id,
                    'amount' => $amount,
                    'amount_formatted' => number_format($amount, 0, ',', '.'),
                    'disbursement_date' => Carbon::parse((string) $row->disbursement_date)->format('Y-m-d'),
                    'mode_value' => $modeValue,
                    'mode_label' => $this->modeLabel($modeValue),
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                    'is_reversed' => $isReversed,
                    'reversal_reason' => $row->reversal_reason !== null ? (string) $row->reversal_reason : null,
                    'reversal_created_at' => $row->reversal_created_at !== null
                        ? Carbon::parse((string) $row->reversal_created_at)->format('Y-m-d H:i')
                        : null,
                ];
            })->values()->all(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
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
