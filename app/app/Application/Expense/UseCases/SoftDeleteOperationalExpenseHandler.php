<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Expense\OperationalExpenseWriterPort;

final class SoftDeleteOperationalExpenseHandler
{
    public function __construct(
        private readonly OperationalExpenseWriterPort $writer,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $expenseId, string $performedByActorId): Result
    {
        $deleted = $this->writer->softDelete($expenseId);

        if ($deleted === false) {
            return Result::failure('Pengeluaran operasional tidak ditemukan atau sudah dihapus.', [
                'expense' => ['OPERATIONAL_EXPENSE_NOT_FOUND'],
            ]);
        }

        $this->audit->record('operational_expense_soft_deleted', [
            'expense_id' => $expenseId,
            'performed_by_actor_id' => $performedByActorId,
        ]);

        return Result::success([
            'expense_id' => $expenseId,
            'deleted' => true,
        ], 'Pengeluaran operasional berhasil dihapus.');
    }
}
