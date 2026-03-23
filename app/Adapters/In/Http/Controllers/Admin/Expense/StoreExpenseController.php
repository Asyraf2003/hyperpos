<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\In\Http\Requests\Expense\StoreExpenseRequest;
use App\Application\Expense\UseCases\RecordOperationalExpenseHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StoreExpenseController extends Controller
{
    public function __invoke(
        StoreExpenseRequest $request,
        RecordOperationalExpenseHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            (string) $data['category_id'],
            (int) $data['amount_rupiah'],
            (string) $data['expense_date'],
            (string) $data['description'],
            (string) $data['payment_method'],
            isset($data['reference_no']) && $data['reference_no'] !== ''
                ? (string) $data['reference_no']
                : null,
            (string) $data['status'],
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'expense' => $result->message() ?? 'Pengeluaran operasional gagal dicatat.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil dicatat.');
    }
}
