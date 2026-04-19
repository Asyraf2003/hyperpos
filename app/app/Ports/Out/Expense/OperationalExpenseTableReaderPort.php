<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

use App\Application\Expense\DTO\ExpenseTableQuery;

interface OperationalExpenseTableReaderPort
{
    /**
     * @return array{
     * rows:list<array{
     * id:string,
     * expense_date:string,
     * category_name:string,
     * category_code:string,
     * description:string,
     * amount_rupiah:int,
     * payment_method:string,
     * status:string,
     * status_label:string,
     * status_badge_class:string
     * }>,
     * meta:array{
     * page:int,
     * per_page:int,
     * total:int,
     * last_page:int,
     * sort_by:string,
     * sort_dir:string,
     * filters:array{
     * q:?string,
     * category_id:?string,
     * date_from:?string,
     * date_to:?string
     * }
     * }
     * }
     */
    public function search(ExpenseTableQuery $query): array;
}
