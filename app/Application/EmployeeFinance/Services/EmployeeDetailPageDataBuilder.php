<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Services;

use App\Ports\Out\EmployeeFinance\EmployeeDebtSummaryByEmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDetailPageReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeePayrollSummaryByEmployeeReaderPort;

final class EmployeeDetailPageDataBuilder
{
    public function __construct(
        private readonly EmployeeDetailPageReaderPort $details,
        private readonly EmployeeDebtSummaryByEmployeeReaderPort $debtSummary,
        private readonly EmployeePayrollSummaryByEmployeeReaderPort $payrollSummary,
    ) {
    }

    /**
     * @return array{
     *     detail: array{summary: array<string, mixed>, page: array<string, mixed>},
     *     page: array<string, mixed>,
     *     debtSummary: array<string, mixed>,
     *     payrollSummary: array<string, mixed>
     * }|null
     */
    public function build(string $employeeId): ?array
    {
        $detail = $this->details->findById($employeeId);

        if ($detail === null) {
            return null;
        }

        return [
            'detail' => $detail,
            'page' => $detail['page'],
            'debtSummary' => $this->debtSummary->findByEmployeeId($employeeId),
            'payrollSummary' => $this->payrollSummary->findByEmployeeId($employeeId),
        ];
    }
}
