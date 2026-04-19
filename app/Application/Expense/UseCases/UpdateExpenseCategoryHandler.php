<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use App\Ports\Out\Expense\ExpenseCategoryWriterPort;

final class UpdateExpenseCategoryHandler
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $readers,
        private readonly ExpenseCategoryWriterPort $writers,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $categoryId, string $code, string $name, ?string $description, string $performedByActorId): Result
    {
        $category = $this->readers->findById($categoryId);

        if ($category === null) {
            return Result::failure('Expense category tidak ditemukan.', ['expense_category' => ['EXPENSE_CATEGORY_NOT_FOUND']]);
        }

        $duplicate = $this->readers->findByCode($code);

        if ($duplicate !== null && $duplicate->id() !== $category->id()) {
            return Result::failure('Kode expense category sudah dipakai.', ['expense_category' => ['EXPENSE_CATEGORY_CODE_ALREADY_EXISTS']]);
        }

        $before = $this->snapshot($category);

        try {
            $category->update($code, $name, $description);
        } catch (DomainException $e) {
            return Result::failure($e->getMessage(), ['expense_category' => ['INVALID_EXPENSE_CATEGORY']]);
        }

        $this->writers->update($category);
        $this->audit->record('expense_category_updated', [
            'category_id' => $category->id(),
            'performed_by_actor_id' => $performedByActorId,
            'before' => $before,
            'after' => $this->snapshot($category),
        ]);

        return Result::success($this->snapshot($category), 'Expense category berhasil diperbarui.');
    }

    private function snapshot(object $category): array
    {
        return [
            'id' => $category->id(),
            'code' => $category->code(),
            'name' => $category->name(),
            'description' => $category->description(),
            'is_active' => $category->isActive(),
        ];
    }
}
