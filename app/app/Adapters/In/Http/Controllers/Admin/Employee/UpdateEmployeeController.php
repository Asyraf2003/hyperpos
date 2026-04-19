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
                (string) $data['employee_name'],
                isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
                isset($data['default_salary_amount']) ? (int) $data['default_salary_amount'] : null,
                (string) $data['salary_basis_type'],
                (string) $data['employment_status'],
                (string) $data['change_reason'],
                (string) $request->user()->getAuthIdentifier(),
                isset($data['started_at']) && $data['started_at'] !== '' ? (string) $data['started_at'] : null,
                isset($data['ended_at']) && $data['ended_at'] !== '' ? (string) $data['ended_at'] : null,
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
