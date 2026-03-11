<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\Supplier\Supplier;
use App\Ports\Out\Procurement\SupplierReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierReaderAdapter implements SupplierReaderPort
{
    public function getByNormalizedNamaPtPengirim(string $namaPtPengirimNormalized): ?Supplier
    {
        $row = DB::table('suppliers')
            ->select(['id', 'nama_pt_pengirim', 'nama_pt_pengirim_normalized'])
            ->where('nama_pt_pengirim_normalized', $namaPtPengirimNormalized)
            ->first();

        if ($row === null) {
            return null;
        }

        return Supplier::rehydrate(
            (string) $row->id,
            (string) $row->nama_pt_pengirim,
        );
    }
}
