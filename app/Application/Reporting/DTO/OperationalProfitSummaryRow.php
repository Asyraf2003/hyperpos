<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class OperationalProfitSummaryRow
{
    public function __construct(
        private readonly string $fromDate,
        private readonly string $toDate,
        private readonly int $grossRevenueRupiah,
        private readonly int $refundedRupiah,
        private readonly int $netRevenueRupiah,
        private readonly int $externalPurchaseCostRupiah,
        private readonly int $storeStockCogsRupiah,
        private readonly int $directCostRupiah,
        private readonly int $grossProfitRupiah,
        private readonly int $operationalExpenseRupiah,
        private readonly int $payrollDisbursementRupiah,
        private readonly int $netOperationalProfitRupiah,
    ) {
    }

    public function toArray(): array
    {
        return [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'gross_revenue_rupiah' => $this->grossRevenueRupiah,
            'refunded_rupiah' => $this->refundedRupiah,
            'net_revenue_rupiah' => $this->netRevenueRupiah,
            'external_purchase_cost_rupiah' => $this->externalPurchaseCostRupiah,
            'store_stock_cogs_rupiah' => $this->storeStockCogsRupiah,
            'direct_cost_rupiah' => $this->directCostRupiah,
            'gross_profit_rupiah' => $this->grossProfitRupiah,
            'operational_expense_rupiah' => $this->operationalExpenseRupiah,
            'payroll_disbursement_rupiah' => $this->payrollDisbursementRupiah,
            'net_operational_profit_rupiah' => $this->netOperationalProfitRupiah,
        ];
    }

    public function grossRevenueRupiah(): int { return $this->grossRevenueRupiah; }
    public function refundedRupiah(): int { return $this->refundedRupiah; }
    public function netRevenueRupiah(): int { return $this->netRevenueRupiah; }
    public function externalPurchaseCostRupiah(): int { return $this->externalPurchaseCostRupiah; }
    public function storeStockCogsRupiah(): int { return $this->storeStockCogsRupiah; }
    public function directCostRupiah(): int { return $this->directCostRupiah; }
    public function grossProfitRupiah(): int { return $this->grossProfitRupiah; }
    public function operationalExpenseRupiah(): int { return $this->operationalExpenseRupiah; }
    public function payrollDisbursementRupiah(): int { return $this->payrollDisbursementRupiah; }
    public function netOperationalProfitRupiah(): int { return $this->netOperationalProfitRupiah; }
}
