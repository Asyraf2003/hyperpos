<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface OperationalExpenseReportingSourceReaderPort
{
    /**
     * @return list<array{
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
     * }>
     */
    public function getOperationalExpenseSummaryRows(
        string $fromExpenseDate,
        string $toExpenseDate,
    ): array;

    /**
     * @return array{
     *   total_rows:int,
     *   total_amount_rupiah:int
     * }
     */
    public function getOperationalExpenseSummaryReconciliation(
        string $fromExpenseDate,
        string $toExpenseDate,
    ): array;
}
