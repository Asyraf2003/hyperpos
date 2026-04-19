<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use App\Ports\Out\Expense\ExpenseCategoryWriterPort;

final class DeactivateExpenseCategoryHandler
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $readers,
        private readonly ExpenseCategoryWriterPort $writers,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $categoryId, string $performedByActorId): Result
    {
        $category = $this->readers->findById($categoryId);

        if ($category === null) {
            return Result::failure('Expense category tidak ditemukan.', ['expense_category' => ['EXPENSE_CATEGORY_NOT_FOUND']]);
        }

        $category->deactivate();
        $this->writers->update($category);
        $this->audit->record('expense_category_deactivated', [
            'category_id' => $category->id(),
            'performed_by_actor_id' => $performedByActorId,
        ]);

        return Result::success(['id' => $category->id(), 'is_active' => false], 'Expense category dinonaktifkan.');
    }
}
