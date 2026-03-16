<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Note\UseCases\CreateNoteHandler;
use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Payment\UseCases\RecordCustomerPaymentHandler;
use App\Application\Procurement\UseCases\CreateSupplierInvoiceHandler;
use App\Core\Note\WorkItem\WorkItem;
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
        $customers = ['Asyraf', 'Liyya', 'Budi Mataram', 'Siti Ampenan', 'Agus Salim', 'Mamat Cakranegara'];
        
        $totalDays = 365;
        $txPerDay = 50;
        
        $bar = $this->command->getOutput()->createProgressBar($totalDays);
        $bar->start();

        for ($i = $totalDays; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            // 1. Refill Stok (Biar gak Insufficient Stock di tengah jalan)
            if ($i % 3 === 0) {
                $this->seedSupplierInvoice($createInvoice, $suppliers, $products, $date);
            }

            // 2. 50 Transaksi Bengkel per Hari
            for ($j = 0; $j < $txPerDay; $j++) {
                $this->simulateRealTransaction(
                    $createNote, $addItem, $recordPayment, 
                    $customers[array_rand($customers)], $products, $date
                );
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->getOutput()->writeln("");
    }

    private function simulateRealTransaction($createNote, $addItem, $recordPayment, $customer, $products, $date): void
    {
        $resNote = $createNote->handle($customer, $date);
        if ($resNote->isFailure()) return;
        $noteId = $resNote->data()['id'];

        // Acak 1-3 pekerjaan per nota
        for ($k = 1; $k <= rand(1, 3); $k++) {
            $type = $this->randomType();
            $p = $products->random();
            
            // SESUAI KONTRAK WorkItemFactory.php
            $sd = ['service_name' => 'Jasa Perbaikan ' . $k, 'service_price_rupiah' => rand(2, 10) * 10000];
            $ext = [['cost_description' => 'Beli Baut/Seal di Luar', 'unit_cost_rupiah' => 5000, 'qty' => 1]];
            $sto = [['product_id' => $p->id, 'qty' => 1, 'line_total_rupiah' => $p->harga_jual]];

            $addItem->handle(
                nId: $noteId, 
                lNo: $k, 
                type: $type, 
                sd: $sd, 
                ext: ($type === WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) ? $ext : [],
                sto: ($type === WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART || $type === WorkItem::TYPE_STORE_STOCK_SALE_ONLY) ? $sto : []
            );
        }

        // 3. ALUR KEUANGAN (Lunas, Cicil, Hutang)
        $total = DB::table('notes')->where('id', $noteId)->value('total_rupiah');
        $dice = rand(1, 100);
        
        if ($dice <= 70) { // 70% Lunas
            $recordPayment->handle((int)$total, $date);
        } elseif ($dice <= 90) { // 20% Bayar Setengah (Piutang)
            $recordPayment->handle((int)($total * 0.5), $date);
        } // 10% sisanya hutang total (tidak panggil recordPayment)
    }

    private function randomType(): string {
        $types = [
            WorkItem::TYPE_SERVICE_ONLY,
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE
        ];
        return $types[array_rand($types)];
    }

    private function seedSupplierInvoice($createInvoice, $suppliers, $products, $date): void {
        $lines = [];
        foreach ($products->random(rand(3, 7)) as $p) {
            $qty = rand(50, 100);
            $lines[] = ['product_id' => $p->id, 'qty_pcs' => $qty, 'line_total_rupiah' => (int)($qty * ($p->harga_jual * 0.6))];
        }
        $createInvoice->handle($suppliers[array_rand($suppliers)], $date, $lines);
    }
}
