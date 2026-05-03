<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;

interface EmployeeDebtTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     employee_id:string,
     *     employee_name:string,
     *     debt_detail_id:?string,
     *     latest_unpaid_debt_id:?string,
     *     total_debt_records:int,
     *     total_debt_amount_formatted:string,
     *     total_remaining_balance_formatted:string,
     *     active_debt_count:int,
     *     paid_debt_count:int,
     *     status_label:string,
     *     latest_recorded_at:string
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
    public function search(EmployeeDebtTableQuery $query): array;
}
