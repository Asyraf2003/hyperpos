<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SupplierPayableReportExcelDetailSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {}

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Detail Hutang Pemasok');
        $values = [];

        foreach (array_values($rows) as $index => $row) {
            $values[] = [
                $index + 1,
                (string) ($row['nomor_faktur'] ?? $row['supplier_invoice_id'] ?? ''),
                (string) ($row['supplier_name'] ?? $row['supplier_id'] ?? ''),
                ViewDateFormatter::display($row['shipment_date'] ?? null),
                ViewDateFormatter::display($row['due_date'] ?? null),
                (string) ($row['due_status_label'] ?? $row['due_status'] ?? ''),
                (int) ($row['grand_total_rupiah'] ?? 0),
                (int) ($row['total_paid_rupiah'] ?? 0),
                (int) ($row['outstanding_rupiah'] ?? 0),
                (int) ($row['receipt_count'] ?? 0),
                (int) ($row['total_received_qty'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'No',
            'No Faktur',
            'Supplier',
            'Tanggal Kirim',
            'Due Date',
            'Status',
            'Total Tagihan',
            'Dibayar',
            'Outstanding',
            'Receipt',
            'Qty Diterima',
        ], $values);

        $this->tables->autosize($sheet, 11);
    }
}
