<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\In\Http\Requests\EmployeeFinance\UpdateEmployeeProfileRequest;
use App\Application\EmployeeFinance\UseCases\UpdateEmployeeProfileHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class UpdateEmployeeController extends Controller
{
    public function __invoke(
        string $employeeId,
        UpdateEmployeeProfileRequest $request,
        UpdateEmployeeProfileHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                $employeeId,
                (string) $data['name'],
                isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
                (int) $data['base_salary_amount'],
                (string) $data['pay_period_value'],
                (string) $data['status_value'],
                (string) $data['change_reason'],
                (string) $request->user()->getAuthIdentifier(),
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Data karyawan gagal diperbarui.';

            return back()
                ->withErrors([
                    'employee' => $message,
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }
}
