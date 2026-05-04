<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Services;

use App\Ports\Out\EmployeeFinance\EmployeeDebtAdjustmentListReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtDetailPageReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtPaymentReversalListReaderPort;

final class EmployeeDebtDetailPageDataBuilder
{
    public function __construct(
        private readonly EmployeeDebtDetailPageReaderPort $details,
        private readonly EmployeeDebtAdjustmentListReaderPort $adjustments,
        private readonly EmployeeDebtPaymentReversalListReaderPort $paymentReversals,
    ) {
    }

    /**
     * @return array{
     *     detail: array<string, mixed>,
     *     adjustments: list<array<string, mixed>>,
     *     paymentReversals: list<array<string, mixed>>
     * }|null
     */
    public function build(string $debtId): ?array
    {
        $detail = $this->details->findById($debtId);

        if ($detail === null) {
            return null;
        }

        return [
            'detail' => $detail,
            'adjustments' => $this->adjustments->findByDebtId($debtId),
            'paymentReversals' => $this->paymentReversals->findByDebtId($debtId),
        ];
    }
}
