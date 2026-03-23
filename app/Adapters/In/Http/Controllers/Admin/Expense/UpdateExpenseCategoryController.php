<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\In\Http\Requests\Expense\UpdateExpenseCategoryRequest;
use App\Application\Expense\UseCases\UpdateExpenseCategoryHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UpdateExpenseCategoryController extends Controller
{
    public function __invoke(
        UpdateExpenseCategoryRequest $request,
        UpdateExpenseCategoryHandler $useCase,
        string $categoryId,
    ): RedirectResponse {
        $result = $useCase->handle(
            $categoryId,
            (string) $request->validated('code'),
            (string) $request->validated('name'),
            $request->validated('description'),
            (string) $request->user()->getAuthIdentifier(),
        );

        if ($result->isFailure()) {
            if (($result->errors()['expense_category'] ?? []) === ['EXPENSE_CATEGORY_NOT_FOUND']) {
                return redirect()
                    ->route('admin.expenses.categories.index')
                    ->with('error', $result->message() ?? 'Expense category tidak ditemukan.');
            }

            return back()
                ->withErrors([
                    'expense_category' => $result->message() ?? 'Expense category gagal diperbarui.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.expenses.categories.index')
            ->with('success', $result->message() ?? 'Expense category berhasil diperbarui.');
    }
}
