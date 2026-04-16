<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\OperationalProfitSummaryRow;

final class OperationalProfitReportingReconciliationService
{
    public function assertOperationalProfitSummaryMatches(
        OperationalProfitSummaryRow $row,
        array $expected,
    ): void {
        if ($row->cashInRupiah() !== $expected['cash_in_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: cash_in_rupiah.');
        }

        if ($row->refundedRupiah() !== $expected['refunded_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: refunded_rupiah.');
        }

        if ($row->externalPurchaseCostRupiah() !== $expected['external_purchase_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: external_purchase_cost_rupiah.');
        }

        if ($row->storeStockCogsRupiah() !== $expected['store_stock_cogs_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: store_stock_cogs_rupiah.');
        }

        if ($row->productPurchaseCostRupiah() !== $expected['product_purchase_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: product_purchase_cost_rupiah.');
        }

        if ($row->operationalExpenseRupiah() !== $expected['operational_expense_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: operational_expense_rupiah.');
        }

        if ($row->payrollDisbursementRupiah() !== $expected['payroll_disbursement_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: payroll_disbursement_rupiah.');
        }

        if ($row->employeeDebtCashOutRupiah() !== $expected['employee_debt_cash_out_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: employee_debt_cash_out_rupiah.');
        }

        if ($row->cashOperationalProfitRupiah() !== $expected['cash_operational_profit_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: cash_operational_profit_rupiah.');
        }
    }
}
