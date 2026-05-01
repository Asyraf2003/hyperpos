<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PayrollReportExcelSummarySheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $summary, array $filters): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Gaji');
        $sheet->setCellValue('A2', 'Periode');
        $sheet->setCellValue('B2', ViewDateFormatter::range($filters['date_from'] ?? null, $filters['date_to'] ?? null));
        $sheet->setCellValue('A3', 'Dasar Tanggal');
        $sheet->setCellValue('B3', 'Tanggal pencairan gaji');

        $this->tables->writeTable($sheet, 5, ['Metrik', 'Nilai'], [
            ['Jumlah Pencairan', (int) ($summary['total_rows'] ?? 0)],
            ['Total Nominal', (int) ($summary['total_amount_rupiah'] ?? 0)],
            ['Tanggal Terakhir', ViewDateFormatter::display($summary['latest_disbursement_date'] ?? null)],
            ['Mode Terbesar', (string) ($summary['top_mode_label'] ?? '')],
            ['Rata-rata Harian', (int) ($summary['average_daily_rupiah'] ?? 0)],
        ]);

        $this->tables->autosize($sheet, 2);
    }
}
