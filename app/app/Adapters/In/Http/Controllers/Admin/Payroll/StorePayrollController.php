<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\In\Http\Requests\EmployeeFinance\DisbursePayrollRequest;
use App\Application\EmployeeFinance\UseCases\DisbursePayrollHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StorePayrollController extends Controller
{
    public function __invoke(
        DisbursePayrollRequest $request,
        DisbursePayrollHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                (string) $data['employee_id'],
                (int) $data['amount'],
                (string) $data['disbursement_date_string'],
                (string) $data['mode_value'],
                isset($data['notes']) && $data['notes'] !== '' ? (string) $data['notes'] : null,
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Pencairan gaji gagal dicatat.';

            return back()
                ->withErrors([
                    'payroll' => $message,
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.payrolls.index')
            ->with('success', 'Pencairan gaji berhasil dicatat.');
    }
}
