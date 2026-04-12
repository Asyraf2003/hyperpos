<?php

// @audit-skip: line-limit
declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\EmployeeDebtReportingSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtReportingSourceReaderAdapter implements EmployeeDebtReportingSourceReaderPort
{
    public function getEmployeeDebtSummaryRows(
        string $fromRecordedDate,
        string $toRecordedDate,
    ): array {
        $paymentTotalsSubquery = DB::table('employee_debt_payments')
            ->leftJoin(
                'employee_debt_payment_reversals',
                'employee_debt_payment_reversals.employee_debt_payment_id',
                '=',
                'employee_debt_payments.id'
            )
            ->whereNull('employee_debt_payment_reversals.id')
            ->selectRaw('employee_debt_payments.employee_debt_id, COALESCE(SUM(employee_debt_payments.amount), 0) as total_paid_amount')
            ->groupBy('employee_debt_payments.employee_debt_id');

        return DB::table('employee_debts')
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.employee_debt_id', '=', 'employee_debts.id');
            })
            ->whereBetween('employee_debts.created_at', [
                $fromRecordedDate . ' 00:00:00',
                $toRecordedDate . ' 23:59:59',
            ])
            ->orderBy('employee_debts.created_at')
            ->orderBy('employee_debts.id')
            ->get([
                'employee_debts.id as debt_id',
                'employee_debts.employee_id',
                'employee_debts.created_at as recorded_at',
                'employee_debts.total_debt',
                DB::raw('COALESCE(payment_totals.total_paid_amount, 0) as total_paid_amount'),
                'employee_debts.remaining_balance',
                'employee_debts.status',
                'employee_debts.notes',
            ])
            ->map(static fn (object $row): array => [
                'debt_id' => (string) $row->debt_id,
                'employee_id' => (string) $row->employee_id,
                'recorded_at' => (string) $row->recorded_at,
                'total_debt' => (int) $row->total_debt,
                'total_paid_amount' => (int) $row->total_paid_amount,
                'remaining_balance' => (int) $row->remaining_balance,
                'status' => (string) $row->status,
                'notes' => $row->notes !== null ? (string) $row->notes : null,
            ])
            ->all();
    }

    public function getEmployeeDebtSummaryReconciliation(
        string $fromRecordedDate,
        string $toRecordedDate,
    ): array {
        $filteredDebtsSubquery = DB::table('employee_debts')
            ->select('id', 'total_debt', 'remaining_balance')
            ->whereBetween('created_at', [
                $fromRecordedDate . ' 00:00:00',
                $toRecordedDate . ' 23:59:59',
            ]);

        $debtTotals = DB::query()
            ->fromSub($filteredDebtsSubquery, 'filtered_debts')
            ->selectRaw(
                'COUNT(*) as total_rows, ' .
                'COALESCE(SUM(total_debt), 0) as total_debt, ' .
                'COALESCE(SUM(remaining_balance), 0) as total_remaining_balance'
            )
            ->first();

        $paymentTotals = DB::table('employee_debt_payments')
            ->joinSub($filteredDebtsSubquery, 'filtered_debts', function ($join): void {
                $join->on('filtered_debts.id', '=', 'employee_debt_payments.employee_debt_id');
            })
            ->leftJoin(
                'employee_debt_payment_reversals',
                'employee_debt_payment_reversals.employee_debt_payment_id',
                '=',
                'employee_debt_payments.id'
            )
            ->whereNull('employee_debt_payment_reversals.id')
            ->selectRaw('COALESCE(SUM(employee_debt_payments.amount), 0) as total_paid_amount')
            ->first();

        return [
            'total_rows' => (int) ($debtTotals->total_rows ?? 0),
            'total_debt' => (int) ($debtTotals->total_debt ?? 0),
            'total_paid_amount' => (int) ($paymentTotals->total_paid_amount ?? 0),
            'total_remaining_balance' => (int) ($debtTotals->total_remaining_balance ?? 0),
        ];
    }
}
