<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\OperationalExpenseSummaryRow;

final class OperationalExpenseSummaryBuilder
{
    /**
     * @param list<array{
     *   expense_id:string,
     *   expense_date:string,
     *   category_id:string,
     *   category_code:string,
     *   category_name:string,
     *   amount_rupiah:int,
     *   description:string,
     *   payment_method:string,
     *   reference_no:?string,
     *   status:string
     * }> $rows
     * @return list<OperationalExpenseSummaryRow>
     */
    public function build(array $rows): array
    {
        return array_map(
            static fn (array $row): OperationalExpenseSummaryRow => new OperationalExpenseSummaryRow(
                $row['expense_id'],
                $row['expense_date'],
                $row['category_id'],
                $row['category_code'],
                $row['category_name'],
                $row['amount_rupiah'],
                $row['description'],
                $row['payment_method'],
                $row['reference_no'],
                $row['status'],
            ),
            $rows,
        );
    }
}
