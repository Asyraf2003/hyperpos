<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeTableQuery;

interface EmployeeTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,
     *     employee_name:string,
     *     phone:?string,
     *     salary_basis_type:string,
     *     salary_basis_label:string,
     *     default_salary_amount:?int,
     *     default_salary_amount_formatted:?string,
     *     employment_status:string,
     *     employment_status_label:string
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int,
     *     sort_by:string,
     *     sort_dir:string,
     *     filters:array{q:?string}
     *   }
     * }
     */
    public function search(EmployeeTableQuery $query): array;
}
