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
        if ($row->grossRevenueRupiah() !== $expected['gross_revenue_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: gross_revenue_rupiah.');
        }

        if ($row->refundedRupiah() !== $expected['refunded_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: refunded_rupiah.');
        }

        if ($row->netRevenueRupiah() !== $expected['net_revenue_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: net_revenue_rupiah.');
        }

        if ($row->externalPurchaseCostRupiah() !== $expected['external_purchase_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: external_purchase_cost_rupiah.');
        }

        if ($row->storeStockCogsRupiah() !== $expected['store_stock_cogs_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: store_stock_cogs_rupiah.');
        }

        if ($row->directCostRupiah() !== $expected['direct_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: direct_cost_rupiah.');
        }

        if ($row->grossProfitRupiah() !== $expected['gross_profit_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: gross_profit_rupiah.');
        }

        if ($row->operationalExpenseRupiah() !== $expected['operational_expense_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: operational_expense_rupiah.');
        }

        if ($row->payrollDisbursementRupiah() !== $expected['payroll_disbursement_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: payroll_disbursement_rupiah.');
        }

        if ($row->netOperationalProfitRupiah() !== $expected['net_operational_profit_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: net_operational_profit_rupiah.');
        }
    }
}
