<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Application\Expense\UseCases\ActivateExpenseCategoryHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ActivateExpenseCategoryController extends Controller
{
    public function __invoke(
        Request $request,
        ActivateExpenseCategoryHandler $useCase,
        string $categoryId,
    ): RedirectResponse {
        $result = $useCase->handle($categoryId, (string) $request->user()->getAuthIdentifier());

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.expenses.categories.index')
                ->with('error', $result->message() ?? 'Expense category tidak ditemukan.');
        }

        return redirect()
            ->route('admin.expenses.categories.index')
            ->with('success', $result->message() ?? 'Expense category diaktifkan.');
    }
}
