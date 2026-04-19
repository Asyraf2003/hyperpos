<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\In\Http\Requests\EmployeeFinance\PayEmployeeDebtRequest;
use App\Application\EmployeeFinance\UseCases\PayEmployeeDebtHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StoreEmployeeDebtPaymentController extends Controller
{
    public function __invoke(
        string $debtId,
        PayEmployeeDebtRequest $request,
        PayEmployeeDebtHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                $debtId,
                (int) $data['payment_amount'],
                isset($data['notes']) && $data['notes'] !== '' ? (string) $data['notes'] : null,
                $request->user() !== null ? (string) $request->user()->getAuthIdentifier() : null,
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Pembayaran hutang gagal dicatat.';

            return back()
                ->withErrors([
                    'debt_payment' => $message,
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.employee-debts.show', ['debtId' => $debtId])
            ->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
