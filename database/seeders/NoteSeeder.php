<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Note\UseCases\CreateNoteHandler;
use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Payment\UseCases\RecordCustomerPaymentHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class NoteSeeder extends Seeder
{
    public function run(
        CreateNoteHandler $createNote,
        AddWorkItemHandler $addItem,
        RecordCustomerPaymentHandler $recordPayment
    ): void {
        $products = DB::table('products')->limit(10)->get();
        if ($products->isEmpty()) return;

        $customers = ['Asyraf', 'Budi Motor', 'Siti Trans', 'Agus Salim', 'Lilik'];

        foreach ($customers as $index => $name) {
            // 1. Create Note (Header)
            $resNote = $createNote->handle($name, now()->format('Y-m-d'));
            if ($resNote->isFailure()) continue;

            $noteId = $resNote->data()['id'];

            // 2. Add Work Items (Skenario Berbeda-beda)
            if ($index % 2 === 0) {
                // Skenario A: Jasa Servis + Barang Gudang
                $p = $products->random();
                $addItem->handle(
                    nId: $noteId,
                    lNo: 1,
                    type: 'service_with_store_stock_part',
                    sd: ['name' => 'Servis Rutin + Ganti Part', 'price' => 50000],
                    sto: [['product_id' => $p->id, 'qty' => 1, 'price' => $p->harga_jual]]
                );
            } else {
                // Skenario B: Jasa Saja
                $addItem->handle(
                    nId: $noteId,
                    lNo: 1,
                    type: 'service_only',
                    sd: ['name' => 'Jasa Cuci Motor', 'price' => 15000]
                );
            }

            // 3. Record Payment (Simulasi Pembayaran Lunas)
            // Note: Untuk benar-benar 'Lunas' di sistem Anda, 
            // biasanya butuh AllocateCustomerPaymentHandler setelah ini.
            $currentNoteTotal = DB::table('notes')->where('id', $noteId)->value('total_rupiah');
            $recordPayment->handle((int)$currentNoteTotal, now()->format('Y-m-d'));
        }
    }
}
