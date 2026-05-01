<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class OperationalExpenseReportExcelDetailSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Detail Biaya');
        $values = [];

        foreach (array_values($rows) as $index => $row) {
            $values[] = [
                $index + 1,
                (string) ($row['expense_id'] ?? ''),
                ViewDateFormatter::display($row['expense_date'] ?? null),
                (string) ($row['category_code'] ?? ''),
                (string) ($row['category_name'] ?? ''),
                (string) ($row['description'] ?? ''),
                $this->paymentMethodLabel((string) ($row['payment_method'] ?? '')),
                (string) ($row['reference_no'] ?? '-'),
                (int) ($row['amount_rupiah'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'No',
            'ID Biaya',
            'Tanggal',
            'Kode Kategori',
            'Kategori',
            'Deskripsi',
            'Metode Pembayaran',
            'Referensi',
            'Nominal',
        ], $values);

        $this->tables->autosize($sheet, 9);
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Tunai',
            'tf', 'transfer', 'bank_transfer' => 'Transfer',
            'debit' => 'Debit',
            'credit' => 'Kredit',
            'qris' => 'QRIS',
            default => strtoupper($method),
        };
    }
}
