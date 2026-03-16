<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Note\UseCases\{CreateNoteHandler, AddWorkItemHandler};
use App\Application\Payment\UseCases\{RecordCustomerPaymentHandler, AllocateCustomerPaymentHandler};
use App\Application\Procurement\UseCases\{CreateSupplierInvoiceHandler, ReceiveSupplierInvoiceHandler};
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class WorkshopStressTestSeeder extends Seeder
{
    public function run(
        CreateNoteHandler $createNote,
        AddWorkItemHandler $addItem,
        RecordCustomerPaymentHandler $recordPayment,
        AllocateCustomerPaymentHandler $allocatePayment,
        CreateSupplierInvoiceHandler $createInvoice,
        ReceiveSupplierInvoiceHandler $receiveInvoice
    ): void {
        $products = DB::table('products')->get();
        if ($products->isEmpty()) return;

        $suppliers = ['PT. Astra Otoparts', 'PT. KYB Indonesia', 'CV. Motor Jaya Mandiri'];
        $customers = ['Asyraf', 'Liyya', 'Budi Mataram', 'Siti Ampenan', 'Agus Salim', 'Mamat Cakranegara'];
        
        $totalDays = 365;
        $txPerDay = 50;
        $bar = $this->command->getOutput()->createProgressBar($totalDays);

        for ($i = $totalDays; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            // 1. PROCUREMENT CYCLE: Pastikan stok masuk dulu
            if ($i % 3 === 0) {
                $this->procurementCycle($createInvoice, $receiveInvoice, $suppliers, $products, $date);
            }

            // 2. NOTE CYCLE
            for ($j = 0; $j < $txPerDay; $j++) {
                $this->workshopCycle($createNote, $addItem, $recordPayment, $allocatePayment, $customers[array_rand($customers)], $products, $date);
            }
            $bar->advance();
        }
        $bar->finish();
    }

    private function procurementCycle($createInvoice, $receiveInvoice, $suppliers, $products, $date): void
    {
        $lines = [];
        $selected = $products->random(rand(5, 10));
        foreach ($selected as $p) {
            $qty = rand(100, 200);
            $lines[] = ['product_id' => $p->id, 'qty_pcs' => $qty, 'line_total_rupiah' => (int)($qty * ($p->harga_jual * 0.6))];
        }

        $resInv = $createInvoice->handle($suppliers[array_rand($suppliers)], $date, $lines);
        if ($resInv->isFailure()) return;
        $invId = $resInv->data()['id'];

        // SINKRONISASI DENGAN SupplierReceiptFactory.php
        $invLines = DB::table('supplier_invoice_lines')->where('supplier_invoice_id', $invId)->get();
        $receiveLines = $invLines->map(fn($l) => [
            'supplier_invoice_line_id' => $l->id, // Key diperbaiki
            'qty_diterima' => $l->qty_pcs         // Key diperbaiki
        ])->toArray();

        $receiveInvoice->handle($invId, $date, $receiveLines);
    }

    private function workshopCycle($createNote, $addItem, $recordPayment, $allocatePayment, $customer, $products, $date): void
    {
        $resNote = $createNote->handle($customer, $date);
        if ($resNote->isFailure()) return;
        $noteId = $resNote->data()['id'];

        for ($k = 1; $k <= rand(1, 3); $k++) {
            $type = $this->randomType();
            $p = $products->random();
            
            // Kontrak WorkItemFactory: service_name, service_price_rupiah, cost_description, line_total_rupiah
            $addItem->handle(
                nId: $noteId, lNo: $k, type: $type,
                sd: ['service_name' => 'Jasa Perbaikan ' . $k, 'service_price_rupiah' => rand(2, 10) * 10000],
                ext: ($type === WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) ? 
                     [['cost_description' => 'Baut Luar', 'unit_cost_rupiah' => 5000, 'qty' => 1]] : [],
                sto: ($type === WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART || $type === WorkItem::TYPE_STORE_STOCK_SALE_ONLY) ? 
                     [['product_id' => $p->id, 'qty' => 1, 'line_total_rupiah' => $p->harga_jual]] : []
            );
        }

        $total = DB::table('notes')->where('id', $noteId)->value('total_rupiah');
        if ($total > 0 && rand(1, 100) <= 85) {
            $resPay = $recordPayment->handle((int)$total, $date);
            if ($resPay->isSuccess()) {
                $allocatePayment->handle($resPay->data()['payment']['id'], $noteId, (int)$total);
            }
        }
    }

    private function randomType(): string {
        $types = [
            WorkItem::TYPE_SERVICE_ONLY, 
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART, 
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE // Ditambahkan!
        ];
        return $types[array_rand($types)];
    }
}
