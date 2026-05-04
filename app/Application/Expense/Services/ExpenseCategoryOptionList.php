<?php

declare(strict_types=1);

namespace App\Application\Expense\Services;

use App\Ports\Out\Expense\ExpenseCategoryOptionReaderPort;

final class ExpenseCategoryOptionList
{
    public function __construct(
        private readonly ExpenseCategoryOptionReaderPort $categories,
    ) {
    }

    /**
     * @return list<array{id:string,label:string}>
     */
    public function active(): array
    {
        return $this->categories->listActiveOptions();
    }
}
