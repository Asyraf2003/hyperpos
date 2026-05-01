<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use Carbon\CarbonImmutable;
use Throwable;

final class TransactionCashLedgerPdfViewDataBuilder
{
    public function build(array $dataset, array $filters): array
    {
        $summary = is_array($dataset['summary'] ?? null) ? $dataset['summary'] : [];
        $rows = is_array($dataset['rows'] ?? null) ? $dataset['rows'] : [];

        return [
            'title' => 'Laporan Buku Kas Transaksi',
            'periodLabel' => $this->formatRange(
                $this->stringValue($filters['date_from'] ?? ''),
                $this->stringValue($filters['date_to'] ?? ''),
            ),
            'generatedAt' => CarbonImmutable::now()->format('d/m/Y H:i'),
            'summaryItems' => $this->summaryItems($summary),
            'rows' => array_map(fn (array $row): array => $this->rowData($row), $rows),
        ];
    }

    private function summaryItems(array $summary): array
    {
        return [
            ['label' => 'Total Kejadian', 'value' => $this->integerValue($summary['total_events'] ?? 0)],
            ['label' => 'Kas Masuk', 'value' => $this->rupiah($summary['total_cash_in_rupiah'] ?? 0)],
            ['label' => 'Kas Keluar', 'value' => $this->rupiah($summary['total_cash_out_rupiah'] ?? 0)],
            ['label' => 'Nilai Bersih', 'value' => $this->rupiah($summary['net_amount_rupiah'] ?? 0)],
        ];
    }

    private function rowData(array $row): array
    {
        $paymentId = $this->stringValue($row['customer_payment_id'] ?? '');
        $refundId = $this->stringValue($row['refund_id'] ?? '');

        return [
            'date' => $this->formatDate($this->stringValue($row['event_date'] ?? '')),
            'note_label' => $this->stringValue($row['note_label'] ?? $row['note_id'] ?? ''),
            'event_type' => $this->eventTypeLabel($this->stringValue($row['event_type'] ?? '')),
            'direction' => $this->directionLabel($this->stringValue($row['direction'] ?? '')),
            'amount' => $this->rupiah($row['event_amount_rupiah'] ?? 0),
            'payment_marker' => $paymentId !== '' ? 'Ada' : '-',
            'refund_marker' => $refundId !== '' ? 'Ada' : '-',
        ];
    }

    private function eventTypeLabel(string $type): string
    {
        return match ($type) {
            'payment_allocation' => 'Alokasi Pembayaran',
            'payment' => 'Pembayaran',
            'refund' => 'Pengembalian Dana',
            default => $type,
        };
    }

    private function directionLabel(string $direction): string
    {
        return match ($direction) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            default => $direction,
        };
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

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
