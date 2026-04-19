<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\EmployeeDebtSummaryRow;

final class EmployeeDebtSummaryBuilder
{
    /**
     * @param list<array{
     *   debt_id:string,
     *   employee_id:string,
     *   recorded_at:string,
     *   total_debt:int,
     *   total_paid_amount:int,
     *   remaining_balance:int,
     *   status:string,
     *   notes:?string
     * }> $rows
     * @return list<EmployeeDebtSummaryRow>
     */
    public function build(array $rows): array
    {
        return array_map(
            static fn (array $row): EmployeeDebtSummaryRow => new EmployeeDebtSummaryRow(
                $row['debt_id'],
                $row['employee_id'],
                $row['recorded_at'],
                $row['total_debt'],
                $row['total_paid_amount'],
                $row['remaining_balance'],
                $row['status'],
                $row['notes'],
            ),
            $rows,
        );
    }
}
