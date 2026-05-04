<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

interface ExpenseCategoryOptionReaderPort
{
    /**
     * @return list<array{id:string,label:string}>
     */
    public function listActiveOptions(): array;
}
