<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use Illuminate\Support\Facades\DB;

final class VoidSupplierInvoiceHandler
{
    public function handle(
        string $supplierInvoiceId,
        string $voidReason,
        ?string $performedByActorId = null,
    ): Result {
        $invoice = DB::table('supplier_invoices')
            ->where('id', trim($supplierInvoiceId))
            ->first([
                'id',
                'voided_at',
            ]);

        if ($invoice === null) {
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        if ($invoice->voided_at !== null) {
            return Result::failure(
                'Nota supplier ini sudah dibatalkan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_ALREADY_VOIDED']]
            );
        }

        $receiptExists = DB::table('supplier_receipts')
            ->where('supplier_invoice_id', trim($supplierInvoiceId))
            ->exists();

        if ($receiptExists) {
            return Result::failure(
                'Nota supplier tidak bisa dibatalkan karena receipt sudah tercatat.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_RECEIPT_EXISTS']]
            );
        }

        $paymentExists = DB::table('supplier_payments')
            ->where('supplier_invoice_id', trim($supplierInvoiceId))
            ->exists();

        if ($paymentExists) {
            return Result::failure(
                'Nota supplier tidak bisa dibatalkan karena pembayaran sudah tercatat.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_PAYMENT_EXISTS']]
            );
        }

        DB::transaction(function () use ($supplierInvoiceId, $voidReason, $performedByActorId): void {
            DB::table('supplier_invoices')
                ->where('id', trim($supplierInvoiceId))
                ->update([
                    'voided_at' => now(),
                    'void_reason' => trim($voidReason),
                ]);

            if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'event' => 'supplier_invoice_voided',
                    'aggregate_type' => 'supplier_invoice',
                    'aggregate_id' => trim($supplierInvoiceId),
                    'actor_id' => $performedByActorId !== null && $performedByActorId !== '' ? $performedByActorId : 'system',
                    'context' => json_encode([
                        'supplier_invoice_id' => trim($supplierInvoiceId),
                        'void_reason' => trim($voidReason),
                        'performed_by_actor_id' => $performedByActorId,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                ]);
            }
        });

        return Result::success(
            ['supplier_invoice_id' => trim($supplierInvoiceId)],
            'Nota supplier berhasil dibatalkan.'
        );
    }
}
