<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\PayrollTableQuery;

interface PayrollTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,employee_id:string,employee_name:string,amount:int,
     *     amount_formatted:string,disbursement_date:string,mode_value:string,
     *     mode_label:string,notes:?string
     *   }>,
     *   meta:array{
     *     page:int,per_page:int,total:int,last_page:int,sort_by:string,sort_dir:string,
     *     filters:array{q:?string}
     *   }
     * }
     */
    public function search(PayrollTableQuery $query): array;
}
