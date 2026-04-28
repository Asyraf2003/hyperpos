<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Shared\DTO\Result;
use Illuminate\Support\Facades\DB;

final class VoidSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceListProjectionService $projection,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        string $voidReason,
        ?string $performedByActorId = null,
    ): Result {
        $normalizedInvoiceId = trim($supplierInvoiceId);

        return DB::transaction(function () use ($normalizedInvoiceId, $voidReason, $performedByActorId): Result {
            $invoice = DB::table('supplier_invoices')
                ->where('id', $normalizedInvoiceId)
                ->lockForUpdate()
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
                ->where('supplier_invoice_id', $normalizedInvoiceId)
                ->exists();

            if ($receiptExists) {
                return Result::failure(
                    'Nota supplier tidak bisa dibatalkan karena receipt sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_RECEIPT_EXISTS']]
                );
            }

            $paymentExists = DB::table('supplier_payments')
                ->where('supplier_invoice_id', $normalizedInvoiceId)
                ->exists();

            if ($paymentExists) {
                return Result::failure(
                    'Nota supplier tidak bisa dibatalkan karena pembayaran sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_PAYMENT_EXISTS']]
                );
            }

            DB::table('supplier_invoices')
                ->where('id', $normalizedInvoiceId)
                ->update([
                    'voided_at' => now(),
                    'void_reason' => trim($voidReason),
                ]);

            if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'event' => 'supplier_invoice_voided',
                    'context' => json_encode([
                        'supplier_invoice_id' => $normalizedInvoiceId,
                        'void_reason' => trim($voidReason),
                        'performed_by_actor_id' => $performedByActorId,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                ]);
            }

            $this->projection->syncInvoice($normalizedInvoiceId);

            return Result::success(
                ['supplier_invoice_id' => $normalizedInvoiceId],
                'Nota supplier berhasil dibatalkan.'
            );
        });
    }
}
