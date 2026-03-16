<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Procurement\Supplier\Supplier;
use App\Ports\Out\Procurement\SupplierWriterPort;
use App\Ports\Out\UuidPort;
use Illuminate\Database\Seeder;

final class SupplierSeeder extends Seeder
{
    public function run(SupplierWriterPort $writer, UuidPort $uuid): void
    {
        $suppliers = [
            ['nama' => 'PT. Astra Otoparts', 'kontak' => '08123456789'],
            ['nama' => 'PT. KYB Indonesia', 'kontak' => '08112233445'],
            ['nama' => 'CV. Motor Jaya Mandiri', 'kontak' => '08556677889'],
        ];

        foreach ($suppliers as $s) {
            $supplier = Supplier::create(
                $uuid->generate(),
                $s['nama'],
                $s['kontak']
            );
            $writer->create($supplier);
        }
    }
}
