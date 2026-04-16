<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class OperationalProfitSummaryRow
{
    public function __construct(
        private readonly string $fromDate,
        private readonly string $toDate,
        private readonly int $cashInRupiah,
        private readonly int $refundedRupiah,
        private readonly int $externalPurchaseCostRupiah,
        private readonly int $storeStockCogsRupiah,
        private readonly int $productPurchaseCostRupiah,
        private readonly int $operationalExpenseRupiah,
        private readonly int $payrollDisbursementRupiah,
        private readonly int $employeeDebtCashOutRupiah,
        private readonly int $cashOperationalProfitRupiah,
    ) {
    }

    public function toArray(): array
    {
        return [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'cash_in_rupiah' => $this->cashInRupiah,
            'refunded_rupiah' => $this->refundedRupiah,
            'external_purchase_cost_rupiah' => $this->externalPurchaseCostRupiah,
            'store_stock_cogs_rupiah' => $this->storeStockCogsRupiah,
            'product_purchase_cost_rupiah' => $this->productPurchaseCostRupiah,
            'operational_expense_rupiah' => $this->operationalExpenseRupiah,
            'payroll_disbursement_rupiah' => $this->payrollDisbursementRupiah,
            'employee_debt_cash_out_rupiah' => $this->employeeDebtCashOutRupiah,
            'cash_operational_profit_rupiah' => $this->cashOperationalProfitRupiah,
        ];
    }

    public function cashInRupiah(): int { return $this->cashInRupiah; }
    public function refundedRupiah(): int { return $this->refundedRupiah; }
    public function externalPurchaseCostRupiah(): int { return $this->externalPurchaseCostRupiah; }
    public function storeStockCogsRupiah(): int { return $this->storeStockCogsRupiah; }
    public function productPurchaseCostRupiah(): int { return $this->productPurchaseCostRupiah; }
    public function operationalExpenseRupiah(): int { return $this->operationalExpenseRupiah; }
    public function payrollDisbursementRupiah(): int { return $this->payrollDisbursementRupiah; }
    public function employeeDebtCashOutRupiah(): int { return $this->employeeDebtCashOutRupiah; }
    public function cashOperationalProfitRupiah(): int { return $this->cashOperationalProfitRupiah; }
}
