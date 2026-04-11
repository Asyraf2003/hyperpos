<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateEmployeeDebtPageController extends Controller
{
    public function __invoke(Request $request, DatabaseEmployeeListPageQuery $employeeQuery): View
    {
        $employees = $employeeQuery->all();
        $prefilledEmployeeId = $this->resolvePrefilledEmployeeId($request->query('employee_id'), $employees);
        $prefilledEmployeeName = $this->resolvePrefilledEmployeeName($prefilledEmployeeId, $employees);

        return view('admin.employee_debts.create', [
            'employees' => $employees,
            'prefilledEmployeeId' => $prefilledEmployeeId,
            'prefilledEmployeeName' => $prefilledEmployeeName,
        ]);
    }

    private function resolvePrefilledEmployeeId(mixed $candidate, array $employees): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        if ($candidate === '') {
            return null;
        }

        foreach ($employees as $employee) {
            if (($employee['id'] ?? null) === $candidate) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolvePrefilledEmployeeName(?string $employeeId, array $employees): ?string
    {
        if ($employeeId === null) {
            return null;
        }

        foreach ($employees as $employee) {
            if (($employee['id'] ?? null) === $employeeId) {
                return is_string($employee['employee_name'] ?? null) ? $employee['employee_name'] : null;
            }
        }

        return null;
    }
}
