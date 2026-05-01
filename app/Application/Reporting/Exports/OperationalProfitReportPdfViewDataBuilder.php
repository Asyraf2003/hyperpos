<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use Carbon\CarbonImmutable;
use Throwable;

final class OperationalProfitReportPdfViewDataBuilder
{
    public function build(array $dataset, array $filters): array
    {
        $row = is_array($dataset['row'] ?? null) ? $dataset['row'] : [];

        return [
            'title' => 'Laporan Laba Kas Operasional',
            'periodLabel' => $this->formatRange(
                $this->stringValue($filters['date_from'] ?? ''),
                $this->stringValue($filters['date_to'] ?? ''),
            ),
            'generatedAt' => CarbonImmutable::now()->format('d/m/Y H:i'),
            'summaryItems' => $this->summaryItems($row),
        ];
    }

    private function summaryItems(array $row): array
    {
        return [
            ['label' => 'Uang Masuk', 'value' => $this->rupiah($row['cash_in_rupiah'] ?? 0)],
            ['label' => 'Pengembalian Dana', 'value' => $this->rupiah($row['refunded_rupiah'] ?? 0)],
            ['label' => 'Pembelian Eksternal', 'value' => $this->rupiah($row['external_purchase_cost_rupiah'] ?? 0)],
            ['label' => 'HPP Stok Toko', 'value' => $this->rupiah($row['store_stock_cogs_rupiah'] ?? 0)],
            ['label' => 'Harga Beli Produk', 'value' => $this->rupiah($row['product_purchase_cost_rupiah'] ?? 0)],
            ['label' => 'Biaya Operasional', 'value' => $this->rupiah($row['operational_expense_rupiah'] ?? 0)],
            ['label' => 'Gaji', 'value' => $this->rupiah($row['payroll_disbursement_rupiah'] ?? 0)],
            ['label' => 'Hutang Karyawan', 'value' => $this->rupiah($row['employee_debt_cash_out_rupiah'] ?? 0)],
            ['label' => 'Laba Kas Operasional', 'value' => $this->rupiah($row['cash_operational_profit_rupiah'] ?? 0)],
        ];
    }

    private function formatRange(string $from, string $to): string
    {
        return $this->formatDate($from).' s/d '.$this->formatDate($to);
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return CarbonImmutable::parse($value)->format('d/m/Y');
        } catch (Throwable) {
            return $value;
        }
    }

    private function rupiah(mixed $value): string
    {
        return 'Rp '.number_format($this->integerValue($value), 0, ',', '.');
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
