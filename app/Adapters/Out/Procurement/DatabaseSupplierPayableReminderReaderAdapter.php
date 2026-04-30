<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Application\Procurement\DTO\SupplierPayableReminderRow;
use App\Ports\Out\Procurement\SupplierPayableReminderReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class DatabaseSupplierPayableReminderReaderAdapter implements SupplierPayableReminderReaderPort
{
    public function findDueReminders(string $today, int $limit = 100): array
    {
        $todayDate = $this->parseDate($today);
        $maxDueDate = $todayDate->modify('+5 days')->format('Y-m-d');

        $paymentTotals = DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id',
            )
            ->whereNull('supplier_payment_reversals.id')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');

        $rows = DB::table('supplier_invoices')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->whereNull('supplier_invoices.voided_at')
            ->whereDate('supplier_invoices.jatuh_tempo', '<=', $maxDueDate)
            ->whereRaw('(supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0)) > 0')
            ->orderBy('supplier_invoices.jatuh_tempo')
            ->orderBy('supplier_invoices.supplier_nama_pt_pengirim_snapshot')
            ->orderBy('supplier_invoices.id')
            ->limit(max(1, $limit))
            ->get([
                'supplier_invoices.id as supplier_invoice_id',
                'supplier_invoices.nomor_faktur',
                'supplier_invoices.supplier_id',
                'supplier_invoices.supplier_nama_pt_pengirim_snapshot as supplier_name',
                'supplier_invoices.tanggal_pengiriman as shipment_date',
                'supplier_invoices.jatuh_tempo as due_date',
                'supplier_invoices.grand_total_rupiah',
                DB::raw('COALESCE(payment_totals.total_paid_rupiah, 0) as total_paid_rupiah'),
                DB::raw('(supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0)) as outstanding_rupiah'),
            ])
            ->all();

        return array_map(
            fn (object $row): SupplierPayableReminderRow => $this->mapRow($row, $todayDate),
            $rows,
        );
    }

    private function mapRow(object $row, DateTimeImmutable $today): SupplierPayableReminderRow
    {
        $dueDate = $this->parseDate((string) $row->due_date);

        return new SupplierPayableReminderRow(
            supplierInvoiceId: (string) $row->supplier_invoice_id,
            nomorFaktur: $row->nomor_faktur !== null ? (string) $row->nomor_faktur : (string) $row->supplier_invoice_id,
            supplierId: (string) $row->supplier_id,
            supplierName: $row->supplier_name !== null ? (string) $row->supplier_name : (string) $row->supplier_id,
            shipmentDate: (string) $row->shipment_date,
            dueDate: $dueDate->format('Y-m-d'),
            grandTotalRupiah: (int) $row->grand_total_rupiah,
            totalPaidRupiah: (int) $row->total_paid_rupiah,
            outstandingRupiah: (int) $row->outstanding_rupiah,
            daysOverdue: $this->daysOverdue($today, $dueDate),
        );
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new InvalidArgumentException('Tanggal supplier payable reminder wajib valid dengan format Y-m-d.');
        }

        return $parsed;
    }

    private function daysOverdue(DateTimeImmutable $today, DateTimeImmutable $dueDate): int
    {
        if ($today <= $dueDate) {
            return 0;
        }

        return (int) $dueDate->diff($today)->days;
    }
}
