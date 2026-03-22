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
            ['nama' => 'PT. Astra Otoparts (Clutch Div)', 'kontak' => '081234567890'],
            ['nama' => 'PT. FCC Indonesia', 'kontak' => '081122334455'],
            ['nama' => 'PT. Exedy Manufacturing Indonesia', 'kontak' => '085566778899'],
            ['nama' => 'PT. Faito Racing Indonesia', 'kontak' => '081299887766'],
            ['nama' => 'PT. TDR Industries', 'kontak' => '081311223344'],
            ['nama' => 'PT. Kawahara Racing', 'kontak' => '085244556677'],
            ['nama' => 'PT. Bintang Racing Team (BRT)', 'kontak' => '082166778899'],
            ['nama' => 'PT. Chemco Harapan Nusantara', 'kontak' => '081988776655'],
            ['nama' => 'PT. Dirgaputra Eta Sembada', 'kontak' => '087855443322'],
            ['nama' => 'PT. Daido Indonesia Manufacturing', 'kontak' => '081233445566'],
            ['nama' => 'PT. Federal Izumi Manufacturing', 'kontak' => '081366554433'],
            ['nama' => 'PT. Showa Indonesia Manufacturing', 'kontak' => '085699887744'],
            ['nama' => 'PT. Musashi Auto Parts Indonesia', 'kontak' => '081122112233'],
            ['nama' => 'PT. Nissin Kogyo Indonesia', 'kontak' => '081200998877'],
            ['nama' => 'PT. Akebono Brake Astra Indonesia', 'kontak' => '082211223344'],
            ['nama' => 'PT. TPR Indonesia', 'kontak' => '081344332211'],
            ['nama' => 'PT. Mikuni Indonesia', 'kontak' => '085355667788'],
            ['nama' => 'PT. Keihin Indonesia', 'kontak' => '081288779900'],
            ['nama' => 'PT. Denso Indonesia', 'kontak' => '081166554422'],
            ['nama' => 'PT. Yamaha Indonesia Motor Mfg (Parts)', 'kontak' => '081922334455'],
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
