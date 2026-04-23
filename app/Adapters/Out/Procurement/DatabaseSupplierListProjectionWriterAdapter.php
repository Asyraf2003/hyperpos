<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierListProjectionWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierListProjectionWriterAdapter implements SupplierListProjectionWriterPort
{
    public function upsert(array $row): void
    {
        DB::table('supplier_list_projection')->updateOrInsert(
            ['supplier_id' => $row['supplier_id']],
            [
                'nama_pt_pengirim' => $row['nama_pt_pengirim'],
                'invoice_count' => $row['invoice_count'],
                'outstanding_rupiah' => $row['outstanding_rupiah'],
                'invoice_unpaid_count' => $row['invoice_unpaid_count'],
                'last_shipment_date' => $row['last_shipment_date'],
                'projected_at' => $row['projected_at'],
            ],
        );
    }
}
