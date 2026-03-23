<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

use App\Application\Expense\DTO\ExpenseCategoryTableQuery;

interface ExpenseCategoryTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,
     *     code:string,
     *     name:string,
     *     description:?string,
     *     is_active:bool,
     *     status_label:string,
     *     status_badge_class:string
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int,
     *     sort_by:string,
     *     sort_dir:string,
     *     filters:array{
     *       q:?string,
     *       is_active:?int
     *     }
     *   }
     * }
     */
    public function search(ExpenseCategoryTableQuery $query): array;
}
