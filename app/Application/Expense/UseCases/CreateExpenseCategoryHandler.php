<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Expense\ExpenseCategory\ExpenseCategory;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use App\Ports\Out\Expense\ExpenseCategoryWriterPort;
use App\Ports\Out\UuidPort;

final class CreateExpenseCategoryHandler
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $expenseCategoryReader,
        private readonly ExpenseCategoryWriterPort $expenseCategoryWriter,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(
        string $code,
        string $name,
        ?string $description = null,
    ): Result {
        if ($this->expenseCategoryReader->existsByCode($code)) {
            return Result::failure(
                'Kode expense category sudah dipakai.',
                ['expense_category' => ['EXPENSE_CATEGORY_CODE_ALREADY_EXISTS']],
            );
        }

        try {
            $expenseCategory = ExpenseCategory::create(
                $this->uuid->generate(),
                $code,
                $name,
                $description,
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['expense_category' => ['INVALID_EXPENSE_CATEGORY']],
            );
        }

        $this->expenseCategoryWriter->create($expenseCategory);

        return Result::success(
            [
                'expense_category' => [
                    'id' => $expenseCategory->id(),
                    'code' => $expenseCategory->code(),
                    'name' => $expenseCategory->name(),
                    'description' => $expenseCategory->description(),
                    'is_active' => $expenseCategory->isActive(),
                ],
            ],
            'Expense category berhasil dibuat.',
        );
    }
}
