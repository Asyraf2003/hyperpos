<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\In\Http\Requests\EmployeeFinance\ReverseEmployeeDebtPaymentRequest;
use App\Application\EmployeeFinance\UseCases\ReverseEmployeeDebtPaymentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StoreEmployeeDebtPaymentReversalController extends Controller
{
    public function __invoke(
        string $paymentId,
        ReverseEmployeeDebtPaymentRequest $request,
        ReverseEmployeeDebtPaymentHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                $paymentId,
                (string) $data['reason'],
                (string) $request->user()->getAuthIdentifier(),
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Reversal pembayaran hutang gagal dicatat.';

            return back()
                ->withErrors(['debt_payment_reversal' => $message])
                ->withInput();
        }

        return back()->with('success', 'Reversal pembayaran hutang berhasil dicatat.');
    }
}
