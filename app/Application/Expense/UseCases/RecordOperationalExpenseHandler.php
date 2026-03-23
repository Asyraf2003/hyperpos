<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Expense\OperationalExpense\OperationalExpense;
use App\Core\Expense\OperationalExpense\OperationalExpenseStatus;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use App\Ports\Out\Expense\OperationalExpenseWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class RecordOperationalExpenseHandler
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $expenseCategoryReader,
        private readonly OperationalExpenseWriterPort $operationalExpenseWriter,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(
        string $categoryId,
        int $amountRupiah,
        string $expenseDate,
        string $description,
        string $paymentMethod,
        ?string $referenceNo = null,
        string $status = OperationalExpenseStatus::POSTED,
    ): Result {
        $category = $this->expenseCategoryReader->findById($categoryId);

        if ($category === null) {
            return Result::failure(
                'Expense category tidak ditemukan.',
                ['expense' => ['EXPENSE_CATEGORY_NOT_FOUND']],
            );
        }

        if ($category->isActive() === false) {
            return Result::failure(
                'Expense category tidak aktif.',
                ['expense' => ['EXPENSE_CATEGORY_INACTIVE']],
            );
        }

        try {
            $operationalExpense = OperationalExpense::create(
                $this->uuid->generate(),
                $categoryId,
                $category->code(),
                $category->name(),
                Money::fromInt($amountRupiah),
                $this->parseExpenseDate($expenseDate),
                $description,
                $paymentMethod,
                $referenceNo,
                $status,
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['expense' => ['INVALID_OPERATIONAL_EXPENSE']],
            );
        }

        $this->operationalExpenseWriter->create($operationalExpense);

        return Result::success(
            [
                'expense' => [
                    'id' => $operationalExpense->id(),
                    'category_id' => $operationalExpense->categoryId(),
                    'category_code_snapshot' => $operationalExpense->categoryCodeSnapshot(),
                    'category_name_snapshot' => $operationalExpense->categoryNameSnapshot(),
                    'amount_rupiah' => $operationalExpense->amountRupiah()->amount(),
                    'expense_date' => $operationalExpense->expenseDate()->format('Y-m-d'),
                    'description' => $operationalExpense->description(),
                    'payment_method' => $operationalExpense->paymentMethod(),
                    'reference_no' => $operationalExpense->referenceNo(),
                    'status' => $operationalExpense->status(),
                ],
            ],
            'Operational expense berhasil dicatat.',
        );
    }

    private function parseExpenseDate(string $expenseDate): DateTimeImmutable
    {
        $normalized = trim($expenseDate);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Expense date wajib berupa tanggal valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
