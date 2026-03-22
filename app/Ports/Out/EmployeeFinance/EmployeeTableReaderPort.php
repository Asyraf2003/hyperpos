<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeTableQuery;

interface EmployeeTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,name:string,phone:?string,base_salary_amount:int,
     *     base_salary_formatted:string,pay_period_value:string,pay_period_label:string,
     *     status_value:string,status_label:string
     *   }>,
     *   meta:array{
     *     page:int,per_page:int,total:int,last_page:int,sort_by:string,sort_dir:string,
     *     filters:array{q:?string}
     *   }
     * }
     */
    public function search(EmployeeTableQuery $query): array;
}
