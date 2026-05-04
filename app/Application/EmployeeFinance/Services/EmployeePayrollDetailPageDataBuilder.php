<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Services;

use App\Ports\Out\EmployeeFinance\EmployeeDetailPageReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeePayrollSummaryByEmployeeReaderPort;

final class EmployeePayrollDetailPageDataBuilder
{
    public function __construct(
        private readonly EmployeeDetailPageReaderPort $details,
        private readonly EmployeePayrollSummaryByEmployeeReaderPort $payrollSummary,
    ) {
    }

    /**
     * @return array{
     *     detail: array{summary: array<string, mixed>, page: array<string, mixed>},
     *     employee: array<string, mixed>,
     *     page: array{heading: string, subtitle: string},
     *     payrollSummary: array<string, mixed>
     * }|null
     */
    public function build(string $employeeId): ?array
    {
        $detail = $this->details->findById($employeeId);

        if ($detail === null) {
            return null;
        }

        $employee = $detail['page']['current_identity'] ?? [];

        if (! is_array($employee)) {
            $employee = [];
        }

        return [
            'detail' => $detail,
            'employee' => $employee,
            'page' => [
                'heading' => 'Detail Gaji Karyawan',
                'subtitle' => 'Riwayat gaji, status pencairan, dan koreksi payroll khusus karyawan ini.',
            ],
            'payrollSummary' => $this->payrollSummary->findByEmployeeId($employeeId),
        ];
    }
}
