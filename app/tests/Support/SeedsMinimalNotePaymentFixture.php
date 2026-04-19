<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsMinimalNotePaymentFixture
{
    private function seedNotePaymentProduct(
        string $id,
        ?string $kodeBarang = null,
        ?string $namaBarang = null,
        string $merek = 'General',
        ?int $ukuran = 100,
        int $hargaJual = 10000
    ): void {
        $kodeBarang ??= strtoupper(str_replace(['_', ' '], '-', $id));
        $namaBarang ??= 'Produk ' . $id;

        DB::table('products')->updateOrInsert(
            ['id' => $id],
            [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'nama_barang_normalized' => $this->normalize($namaBarang),
                'merek' => $merek,
                'merek_normalized' => $this->normalize($merek),
                'ukuran' => $ukuran,
                'harga_jual' => $hargaJual,
                'deleted_at' => null,
                'deleted_by_actor_id' => null,
                'delete_reason' => null,
            ]
        );
    }

    private function seedNoteBase(
        string $id,
        string $customerName,
        string $transactionDate,
        int $totalRupiah,
        string $noteState = 'open'
    ): void {
        DB::table('notes')->updateOrInsert(
            ['id' => $id],
            [
                'customer_name' => $customerName,
                'customer_phone' => null,
                'transaction_date' => $transactionDate,
                'note_state' => $noteState,
                'closed_at' => null,
                'closed_by_actor_id' => null,
                'reopened_at' => null,
                'reopened_by_actor_id' => null,
                'total_rupiah' => $totalRupiah,
            ]
        );
    }

    private function seedWorkItemBase(
        string $id,
        string $noteId,
        int $lineNo,
        string $transactionType,
        string $status,
        int $subtotalRupiah
    ): void {
        DB::table('work_items')->updateOrInsert(
            ['id' => $id],
            [
                'note_id' => $noteId,
                'line_no' => $lineNo,
                'transaction_type' => $transactionType,
                'status' => $status,
                'subtotal_rupiah' => $subtotalRupiah,
            ]
        );
    }

    private function seedServiceDetailBase(
        string $workItemId,
        string $serviceName,
        int $servicePriceRupiah,
        string $partSource
    ): void {
        DB::table('work_item_service_details')->updateOrInsert(
            ['work_item_id' => $workItemId],
            [
                'service_name' => $serviceName,
                'service_price_rupiah' => $servicePriceRupiah,
                'part_source' => $partSource,
            ]
        );
    }

    private function seedStoreStockLineBase(
        string $id,
        string $workItemId,
        string $productId,
        int $qty,
        int $lineTotalRupiah
    ): void {
        DB::table('work_item_store_stock_lines')->updateOrInsert(
            ['id' => $id],
            [
                'work_item_id' => $workItemId,
                'product_id' => $productId,
                'qty' => $qty,
                'line_total_rupiah' => $lineTotalRupiah,
            ]
        );
    }

    private function seedCustomerPaymentBase(
        string $id,
        int $amountRupiah,
        string $paidAt
    ): void {
        DB::table('customer_payments')->updateOrInsert(
            ['id' => $id],
            [
                'amount_rupiah' => $amountRupiah,
                'paid_at' => $paidAt,
            ]
        );
    }

    private function seedPaymentAllocationBase(
        string $id,
        string $customerPaymentId,
        string $noteId,
        int $amountRupiah
    ): void {
        DB::table('payment_allocations')->updateOrInsert(
            ['id' => $id],
            [
                'customer_payment_id' => $customerPaymentId,
                'note_id' => $noteId,
                'amount_rupiah' => $amountRupiah,
            ]
        );
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($value);
    }
}
