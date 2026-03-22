<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\In\Http\Requests\EmployeeFinance\DisbursePayrollBatchRequest;
use App\Application\EmployeeFinance\UseCases\DisbursePayrollBatchHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StorePayrollBatchController extends Controller
{
    public function __invoke(
        DisbursePayrollBatchRequest $request,
        DisbursePayrollBatchHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            (string) $request->user()->getAuthIdentifier(),
            (string) $data['disbursement_date_string'],
            (string) $data['mode_value'],
            isset($data['notes']) && $data['notes'] !== '' ? (string) $data['notes'] : null,
            $data['rows'],
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors(['payroll_batch' => $result->message() ?? 'Batch payroll gagal dicatat.'])
                ->withInput();
        }

        return redirect()
            ->route('admin.payrolls.index')
            ->with('success', $result->message() ?? 'Batch payroll berhasil dicatat.');
    }
}
