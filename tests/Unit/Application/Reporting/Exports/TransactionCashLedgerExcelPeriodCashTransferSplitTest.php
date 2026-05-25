<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reporting\Exports;

use App\Application\Reporting\Exports\TransactionCashLedgerExcelPeriodSheetWriter;
use App\Application\Reporting\Exports\TransactionReportExcelTableWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

final class TransactionCashLedgerExcelPeriodCashTransferSplitTest extends TestCase
{
    public function test_excel_period_sheet_exposes_cash_and_transfer_money_in_split(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $writer = new TransactionCashLedgerExcelPeriodSheetWriter(
            new TransactionReportExcelTableWriter(),
        );

        $writer->write($sheet, [[
            'period_label' => '2030-01-31',
            'total_events' => 3,
            'total_in_rupiah' => 115000,
            'cash_in_rupiah' => 85000,
            'transfer_in_rupiah' => 30000,
            'cash_out_rupiah' => 10000,
            'net_amount_rupiah' => 105000,
        ]]);

        $this->assertSame('Kas Masuk', $sheet->getCell('C1')->getValue());
        $this->assertSame(115000, $sheet->getCell('C2')->getValue());

        $this->assertSame('Tunai Masuk', $sheet->getCell('D1')->getValue());
        $this->assertSame(85000, $sheet->getCell('D2')->getValue());

        $this->assertSame('Transfer Masuk', $sheet->getCell('E1')->getValue());
        $this->assertSame(30000, $sheet->getCell('E2')->getValue());

        $this->assertSame('Kas Keluar', $sheet->getCell('F1')->getValue());
        $this->assertSame(10000, $sheet->getCell('F2')->getValue());

        $this->assertSame('Nilai Bersih', $sheet->getCell('G1')->getValue());
        $this->assertSame(105000, $sheet->getCell('G2')->getValue());

        $spreadsheet->disconnectWorksheets();
    }
}
