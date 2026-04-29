<?php

declare(strict_types=1);

namespace Tests\Support\Procurement;

use Illuminate\Support\Facades\DB;

trait SupplierPayableReminderFixtures
{
    private function seedSupplierPayableInvoice(
        string $key,
        string $dueDate,
        int $grandTotalRupiah,
        string $supplierName,
    ): string {
        $supplierId = 'supplier-'.$key;
        $invoiceId = 'invoice-'.$key;

        DB::table('suppliers')->insert([
            'id' => $supplierId,
            'nama_pt_pengirim' => $supplierName,
            'nama_pt_pengirim_normalized' => mb_strtolower($supplierName, 'UTF-8'),
        ]);

        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => $supplierId,
            'supplier_nama_pt_pengirim_snapshot' => $supplierName,
            'nomor_faktur' => 'NF-'.$key,
            'nomor_faktur_normalized' => 'nf-'.$key,
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'tanggal_pengiriman' => '2026-04-01',
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotalRupiah,
            'last_revision_no' => 0,
        ]);

        return $invoiceId;
    }

    private function seedSupplierPayment(string $paymentId, string $invoiceId, int $amountRupiah): void
    {
        DB::table('supplier_payments')->insert([
            'id' => $paymentId,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => '2026-04-10',
            'proof_status' => 'uploaded',
        ]);
    }

    private function seedPushSubscription(int $userId, string $browser): void
    {
        $endpoint = 'https://push.example.test/send/'.$browser;

        DB::table('push_subscriptions')->insert([
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'endpoint_hash' => hash('sha256', $endpoint),
            'public_key' => 'public-key-'.$browser,
            'auth_token' => 'auth-token-'.$browser,
            'content_encoding' => 'aes128gcm',
            'user_agent' => 'Feature Test '.$browser,
            'last_seen_at' => '2026-04-25 10:00:00',
            'created_at' => '2026-04-25 10:00:00',
            'updated_at' => '2026-04-25 10:00:00',
        ]);
    }
}
