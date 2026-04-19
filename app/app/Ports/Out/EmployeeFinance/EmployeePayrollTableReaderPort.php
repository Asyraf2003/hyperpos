<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeePayrollTableQuery;

interface EmployeePayrollTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,
     *     amount:int,
     *     amount_formatted:string,
     *     disbursement_date:string,
     *     mode_value:string,
     *     mode_label:string,
     *     notes:?string,
     *     is_reversed:bool,
     *     reversal_reason:?string,
     *     reversal_created_at:?string
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int
     *   }
     * }
     */
    public function search(string $employeeId, EmployeePayrollTableQuery $query): array;
}
