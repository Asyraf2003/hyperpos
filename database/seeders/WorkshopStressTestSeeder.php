<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Note\UseCases\CreateNoteHandler;
use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Payment\UseCases\RecordCustomerPaymentHandler;
use App\Application\Procurement\UseCases\CreateSupplierInvoiceHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class WorkshopStressTestSeeder extends Seeder
{
    public function run(
        CreateNoteHandler $createNote,
        AddWorkItemHandler $addItem,
        RecordCustomerPaymentHandler $recordPayment,
        CreateSupplierInvoiceHandler $createInvoice
    ): void {
        $products = DB::table('products')->get();
        if ($products->isEmpty()) return;

        $suppliers = ['PT. Astra Otoparts', 'PT. KYB Indonesia', 'CV. Motor Jaya Mandiri'];
        $customers = ['Asyraf', 'Liyya', 'Budi', 'Siti', 'Agus', 'Mamat', 'Eko', 'Rina'];
        
        $totalDays = 365;
        $txPerDay = 50;
        
        $bar = $this->command->getOutput()->createProgressBar($totalDays);
        $bar->start();

        for ($i = $totalDays; $i >= 0; $i--) {
            $currentDate = now()->subDays($i)->format('Y-m-d');

            // 1. Simulasi Stok Masuk (Setiap 3 hari sekali ada kiriman barang)
            if ($i % 3 === 0) {
                $this->seedSupplierInvoice($createInvoice, $suppliers, $products, $currentDate);
            }

            // 2. Simulasi 50 Transaksi Bengkel
            for ($j = 0; $j < $txPerDay; $j++) {
                $this->simulateTransaction(
                    $createNote, 
                    $addItem, 
                    $recordPayment, 
                    $customers[array_rand($customers)], 
                    $products, 
                    $currentDate
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->getOutput()->writeln("");
    }

    private function simulateTransaction($createNote, $addItem, $recordPayment, $customer, $products, $date): void
    {
        // A. Buka Nota
        $resNote = $createNote->handle($customer, $date);
        if ($resNote->isFailure()) return;
        $noteId = $resNote->data()['id'];

        // B. Tambah 1-3 Pekerjaan secara acak
        $itemCount = rand(1, 3);
        for ($k = 1; $k <= $itemCount; $k++) {
            $p = $products->random();
            $type = (rand(0, 1) === 1) ? 'service_with_store_stock_part' : 'service_only';

            $addItem->handle(
                nId: $noteId,
                lNo: $k,
                type: $type,
                sd: ['name' => 'Pekerjaan ' . $k, 'price' => rand(20, 100) * 1000],
                sto: ($type === 'service_with_store_stock_part') ? 
                     [['product_id' => $p->id, 'qty' => 1, 'price' => $p->harga_jual]] : []
            );
        }

        // C. Bayar Lunas
        $total = DB::table('notes')->where('id', $noteId)->value('total_rupiah');
        $recordPayment->handle((int)$total, $date);
    }

    private function seedSupplierInvoice($createInvoice, $suppliers, $products, $date): void
    {
        $lines = [];
        $selectedProducts = $products->random(rand(5, 10));
        foreach ($selectedProducts as $p) {
            $qty = rand(20, 50);
            $lines[] = [
                'product_id' => $p->id,
                'qty_pcs' => $qty,
                'line_total_rupiah' => (int)($qty * ($p->harga_jual * 0.7))
            ];
        }
        $createInvoice->handle($suppliers[array_rand($suppliers)], $date, $lines);
    }
}
