<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\In\Http\Requests\EmployeeFinance\AdjustEmployeeDebtPrincipalRequest;
use App\Application\EmployeeFinance\UseCases\AdjustEmployeeDebtPrincipalHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StoreEmployeeDebtAdjustmentController extends Controller
{
    public function __invoke(
        string $debtId,
        AdjustEmployeeDebtPrincipalRequest $request,
        AdjustEmployeeDebtPrincipalHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                $debtId,
                (string) $data['adjustment_type'],
                (int) $data['amount'],
                (string) $data['reason'],
                (string) $request->user()->getAuthIdentifier(),
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Koreksi hutang gagal dicatat.';

            return back()
                ->withErrors(['debt_adjustment' => $message])
                ->withInput();
        }

        return redirect()
            ->route('admin.employee-debts.show', ['debtId' => $debtId])
            ->with('success', 'Koreksi hutang berhasil dicatat.');
    }
}
