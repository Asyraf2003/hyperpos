<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Application\Expense\UseCases\SoftDeleteOperationalExpenseHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class SoftDeleteOperationalExpenseController extends Controller
{
    public function __invoke(
        Request $request,
        SoftDeleteOperationalExpenseHandler $useCase,
        string $expenseId,
    ): RedirectResponse {
        $result = $useCase->handle($expenseId, (string) $request->user()->getAuthIdentifier());

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.expenses.index')
                ->with('error', $result->message() ?? 'Pengeluaran operasional tidak ditemukan.');
        }

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', $result->message() ?? 'Pengeluaran operasional berhasil dihapus.');
    }
}
