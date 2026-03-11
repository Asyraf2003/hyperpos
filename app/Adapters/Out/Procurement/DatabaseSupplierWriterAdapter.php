<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\Supplier\Supplier;
use App\Ports\Out\Procurement\SupplierWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierWriterAdapter implements SupplierWriterPort
{
    public function create(Supplier $supplier): void
    {
        DB::table('suppliers')->insert($this->toRecord($supplier));
    }

    /**
     * @return array<string, string>
     */
    private function toRecord(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id(),
            'nama_pt_pengirim' => $supplier->namaPtPengirim(),
            'nama_pt_pengirim_normalized' => $supplier->namaPtPengirimNormalized(),
        ];
    }
}
