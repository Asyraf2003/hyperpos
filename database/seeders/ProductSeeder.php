<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ProductSeeder extends Seeder
{
    public function run(CreateProductHandler $handler): void
    {
        $data = $this->generateData();

        foreach ($data as $item) {
            $result = $handler->handle(
                kodeBarang: $item['kode_barang'],
                namaBarang: $item['nama_barang'],
                merek: $item['merek'],
                ukuran: $item['ukuran'],
                hargaJual: $item['harga_jual']
            );

            if ($result->isFailure()) {
                Log::warning("Seeder Gagal: " . $result->message(), $item);
            }
        }
    }

    private function generateData(): array
    {
        $motorList = [
            'Beat', 'Vario', 'Scoopy', 'PCX', 'Mio', 
            'NMAX', 'Aerox', 'Vixion', 'CB150R', 'Supra X', 
            'Jupiter Z', 'Satria FU', 'Tiger', 'Megalodon', 'FizR'
        ];

        $categories = [
            ['nama' => 'Shockbreaker', 'merek' => ['KYB', 'Showa']],
            ['nama' => 'Piston Kit', 'merek' => ['FIM', 'NPP']]
        ];

        $ukuranList = [0, 25, 50, 75, 100, 125, 150, 175, 200, 225, 250, 275, 300];
        $products = [];

        foreach ($motorList as $motor) {
            foreach ($categories as $cat) {
                foreach ($cat['merek'] as $merek) {
                    foreach ($ukuranList as $ukuran) {
                        $products[] = [
                            'kode_barang' => 'PRD-' . Str::upper(Str::random(8)),
                            'nama_barang' => "{$cat['nama']} {$motor}",
                            'merek' => $merek,
                            'ukuran' => $ukuran,
                            'harga_jual' => $this->calculatePrice($cat['nama'], $ukuran),
                        ];
                    }
                }
            }
        }

        return $products;
    }

    private function calculatePrice(string $kategori, int $ukuran): int
    {
        $base = ($kategori === 'Shockbreaker') ? 250000 : 150000;

        // Logika 0-75 harga sama, 100-175 harga sama, dst
        if ($ukuran <= 75) {
            return $base;
        } elseif ($ukuran <= 175) {
            return $base + 50000;
        } else {
            return $base + 100000;
        }
    }
}
