<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use Carbon\CarbonImmutable;
use Throwable;

final class InventoryStockValueReportPdfViewDataBuilder
{
    public function build(array $dataset, array $filters): array
    {
        $summary = is_array($dataset['summary'] ?? null) ? $dataset['summary'] : [];
        $snapshotRows = is_array($dataset['snapshot_rows'] ?? null) ? $dataset['snapshot_rows'] : [];
        $movementRows = is_array($dataset['movement_rows'] ?? null) ? $dataset['movement_rows'] : [];

        return [
            'title' => 'Stok dan Nilai Persediaan',
            'periodLabel' => $this->formatRange(
                $this->stringValue($filters['date_from'] ?? ''),
                $this->stringValue($filters['date_to'] ?? ''),
            ),
            'referenceDateLabel' => $this->formatDate($this->stringValue($filters['reference_date'] ?? '')),
            'generatedAt' => CarbonImmutable::now()->format('d/m/Y H:i'),
            'summaryItems' => $this->summaryItems($summary),
            'movementRows' => array_map(fn (array $row): array => $this->movementRowData($row), $movementRows),
            'snapshotRows' => array_map(fn (array $row): array => $this->snapshotRowData($row), $snapshotRows),
        ];
    }

    private function summaryItems(array $summary): array
    {
        return [
            ['label' => 'Produk Snapshot', 'value' => $this->integerValue($summary['snapshot_product_rows'] ?? 0)],
            ['label' => 'Produk Bermutasi', 'value' => $this->integerValue($summary['movement_product_rows'] ?? 0)],
            ['label' => 'Qty Tersedia', 'value' => $this->integerValue($summary['total_qty_on_hand'] ?? 0)],
            ['label' => 'Nilai Persediaan', 'value' => $this->rupiah($summary['total_inventory_value_rupiah'] ?? 0)],
            ['label' => 'Qty Masuk Pembelian', 'value' => $this->integerValue($summary['period_supply_in_qty'] ?? 0)],
            ['label' => 'Qty Keluar Penjualan', 'value' => $this->integerValue($summary['period_sale_out_qty'] ?? 0)],
            ['label' => 'Qty Balik Refund/Reversal', 'value' => $this->integerValue($summary['period_refund_reversal_qty'] ?? 0)],
            ['label' => 'Qty Koreksi/Revisi', 'value' => $this->integerValue($summary['period_revision_correction_qty'] ?? 0)],
            ['label' => 'Selisih Qty Periode', 'value' => $this->integerValue($summary['period_net_qty_delta'] ?? 0)],
            ['label' => 'Nilai Masuk Periode', 'value' => $this->rupiah($summary['period_total_in_cost_rupiah'] ?? 0)],
            ['label' => 'Nilai Keluar Periode', 'value' => $this->rupiah($summary['period_total_out_cost_rupiah'] ?? 0)],
            ['label' => 'Selisih Nilai Pokok Periode', 'value' => $this->rupiah($summary['period_net_cost_delta_rupiah'] ?? 0)],
            ['label' => 'Produk Aman', 'value' => $this->integerValue($summary['stock_safe_product_rows'] ?? 0)],
            ['label' => 'Produk Low', 'value' => $this->integerValue($summary['stock_low_product_rows'] ?? 0)],
            ['label' => 'Produk Critical', 'value' => $this->integerValue($summary['stock_critical_product_rows'] ?? 0)],
            ['label' => 'Produk Belum Konfigurasi Threshold', 'value' => $this->integerValue($summary['stock_unconfigured_product_rows'] ?? 0)],
        ];
    }

    private function movementRowData(array $row): array
    {
        return [
            'kode_barang' => $this->stringValue($row['kode_barang'] ?? ''),
            'nama_barang' => $this->stringValue($row['nama_barang'] ?? ''),
            'supply_in_qty' => $this->integerValue($row['supply_in_qty'] ?? 0),
            'sale_out_qty' => $this->integerValue($row['sale_out_qty'] ?? 0),
            'refund_reversal_qty' => $this->integerValue($row['refund_reversal_qty'] ?? 0),
            'revision_correction_qty' => $this->integerValue($row['revision_correction_qty'] ?? 0),
            'net_qty_delta' => $this->integerValue($row['net_qty_delta'] ?? 0),
            'net_cost_delta' => $this->rupiah($row['net_cost_delta_rupiah'] ?? 0),
            'current_qty_on_hand' => $this->integerValue($row['current_qty_on_hand'] ?? 0),
            'current_inventory_value' => $this->rupiah($row['current_inventory_value_rupiah'] ?? 0),
        ];
    }

    private function snapshotRowData(array $row): array
    {
        return [
            'kode_barang' => $this->stringValue($row['kode_barang'] ?? ''),
            'nama_barang' => $this->stringValue($row['nama_barang'] ?? ''),
            'merek' => $this->stringValue($row['merek'] ?? ''),
            'ukuran' => $this->nullableIntegerValue($row['ukuran'] ?? null),
            'current_qty_on_hand' => $this->integerValue($row['current_qty_on_hand'] ?? 0),
            'current_avg_cost' => $this->rupiah($row['current_avg_cost_rupiah'] ?? 0),
            'current_inventory_value' => $this->rupiah($row['current_inventory_value_rupiah'] ?? 0),
            'reorder_point_qty' => $this->nullableIntegerValue($row['reorder_point_qty'] ?? null),
            'critical_threshold_qty' => $this->nullableIntegerValue($row['critical_threshold_qty'] ?? null),
        ];
    }

    private function formatRange(string $from, string $to): string
    {
        return $this->formatDate($from) . ' s/d ' . $this->formatDate($to);
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
        return 'Rp ' . number_format($this->integerValue($value), 0, ',', '.');
    }

    private function nullableIntegerValue(mixed $value): string|int
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return $this->integerValue($value);
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
