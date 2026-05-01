<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class TransactionCashLedgerExcelSummarySheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $summary, array $filters): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Buku Kas Transaksi');
        $sheet->setCellValue('A2', 'Periode');
        $sheet->setCellValue('B2', ViewDateFormatter::range($filters['date_from'] ?? null, $filters['date_to'] ?? null));
        $sheet->setCellValue('A3', 'Dasar Tanggal');
        $sheet->setCellValue('B3', 'Tanggal kejadian kas');

        $this->tables->writeTable($sheet, 5, ['Metrik', 'Nilai'], [
            ['Total Kejadian', (int) ($summary['total_events'] ?? 0)],
            ['Kas Masuk', (int) ($summary['total_cash_in_rupiah'] ?? 0)],
            ['Kas Keluar', (int) ($summary['total_cash_out_rupiah'] ?? 0)],
            ['Nilai Bersih', (int) ($summary['net_amount_rupiah'] ?? 0)],
        ]);

        $this->tables->autosize($sheet, 2);
    }
}
