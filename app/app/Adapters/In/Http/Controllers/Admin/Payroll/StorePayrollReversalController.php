<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\In\Http\Requests\EmployeeFinance\ReversePayrollDisbursementRequest;
use App\Application\EmployeeFinance\UseCases\ReversePayrollDisbursementHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StorePayrollReversalController extends Controller
{
    public function __invoke(
        string $payrollId,
        ReversePayrollDisbursementRequest $request,
        ReversePayrollDisbursementHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                $payrollId,
                (string) $data['reason'],
                (string) $request->user()->getAuthIdentifier(),
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Pembatalan pencairan gaji gagal dicatat.';

            return back()
                ->withErrors(['payroll_reversal' => $message])
                ->withInput();
        }

        return back()->with('success', 'Pencairan gaji berhasil dibatalkan.');
    }
}
