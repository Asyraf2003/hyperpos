<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetTransactionCashLedgerPerNoteHandler;
use App\Application\Reporting\UseCases\GetTransactionReportDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RefundedNoteCashReportingFallbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_fully_refunded_note_with_empty_payment_allocations_reports_zero_net_cash_and_zero_outstanding(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-refunded-empty-allocation',
            'current_revision_id' => 'note-refunded-empty-allocation-r002',
            'latest_revision_number' => 2,
            'customer_name' => 'Pelanggan Refund',
            'transaction_date' => '2030-01-15',
            'note_state' => 'refunded',
            'total_rupiah' => 0,
            'due_date' => '2030-02-15',
        ]);

        DB::table('customer_payments')->insert([
            [
                'id' => 'payment-refund-a',
                'amount_rupiah' => 265000,
                'paid_at' => '2030-01-15',
            ],
            [
                'id' => 'payment-refund-b',
                'amount_rupiah' => 445800,
                'paid_at' => '2030-01-15',
            ],
        ]);

        DB::table('customer_refunds')->insert([
            [
                'id' => 'refund-a1',
                'customer_payment_id' => 'payment-refund-a',
                'note_id' => 'note-refunded-empty-allocation',
                'amount_rupiah' => 122000,
                'refunded_at' => '2030-01-15',
                'reason' => 'refund old line',
            ],
            [
                'id' => 'refund-a2',
                'customer_payment_id' => 'payment-refund-a',
                'note_id' => 'note-refunded-empty-allocation',
                'amount_rupiah' => 143000,
                'refunded_at' => '2030-01-15',
                'reason' => 'refund replacement line',
            ],
            [
                'id' => 'refund-b1',
                'customer_payment_id' => 'payment-refund-b',
                'note_id' => 'note-refunded-empty-allocation',
                'amount_rupiah' => 445800,
                'refunded_at' => '2030-01-15',
                'reason' => 'refund remaining components',
            ],
        ]);

        $transactionResult = app(GetTransactionReportDatasetHandler::class)
            ->handle('2030-01-01', '2030-01-31');

        $this->assertTrue($transactionResult->isSuccess());

        $transactionData = $transactionResult->data();
        $this->assertIsArray($transactionData);

        $this->assertSame([
            'total_rows' => 1,
            'gross_transaction_rupiah' => 0,
            'allocated_payment_rupiah' => 710800,
            'refunded_rupiah' => 710800,
            'net_cash_collected_rupiah' => 0,
            'outstanding_rupiah' => 0,
            'settled_rows' => 1,
            'outstanding_rows' => 0,
        ], $transactionData['summary']);

        $ledgerResult = app(GetTransactionCashLedgerPerNoteHandler::class)
            ->handle('2030-01-01', '2030-01-31');

        $this->assertTrue($ledgerResult->isSuccess());

        $ledgerData = $ledgerResult->data();
        $this->assertIsArray($ledgerData);

        $ledgerRows = $ledgerData['rows'];
        $this->assertSame(710800, array_sum(array_column(
            array_filter($ledgerRows, static fn (array $row): bool => $row['direction'] === 'in'),
            'event_amount_rupiah'
        )));
        $this->assertSame(710800, array_sum(array_column(
            array_filter($ledgerRows, static fn (array $row): bool => $row['direction'] === 'out'),
            'event_amount_rupiah'
        )));
    }
}
