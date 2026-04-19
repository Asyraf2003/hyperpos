<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface EmployeeDebtReportingSourceReaderPort
{
    /**
     * @return list<array{
     *   debt_id:string,
     *   employee_id:string,
     *   recorded_at:string,
     *   total_debt:int,
     *   total_paid_amount:int,
     *   remaining_balance:int,
     *   status:string,
     *   notes:?string
     * }>
     */
    public function getEmployeeDebtSummaryRows(
        string $fromRecordedDate,
        string $toRecordedDate,
    ): array;

    /**
     * @return array{
     *   total_rows:int,
     *   total_debt:int,
     *   total_paid_amount:int,
     *   total_remaining_balance:int
     * }
     */
    public function getEmployeeDebtSummaryReconciliation(
        string $fromRecordedDate,
        string $toRecordedDate,
    ): array;
}
