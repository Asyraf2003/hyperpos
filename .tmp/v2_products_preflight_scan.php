<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$totalProducts = (int) DB::table('products')->count();
$nullUkuranCount = (int) DB::table('products')->whereNull('ukuran')->count();
$nonNullUkuranCount = (int) DB::table('products')->whereNotNull('ukuran')->count();

$duplicateBusinessKeys = DB::table('products')
    ->selectRaw('nama_barang, merek, ukuran, COUNT(*) as row_count')
    ->groupBy('nama_barang', 'merek', 'ukuran')
    ->havingRaw('COUNT(*) > 1')
    ->orderByDesc('row_count')
    ->limit(20)
    ->get()
    ->map(fn ($row) => [
        'nama_barang' => $row->nama_barang,
        'merek' => $row->merek,
        'ukuran' => $row->ukuran,
        'row_count' => (int) $row->row_count,
    ])
    ->all();

$duplicateKodeBarang = DB::table('products')
    ->selectRaw('kode_barang, COUNT(*) as row_count')
    ->whereNotNull('kode_barang')
    ->where('kode_barang', '<>', '')
    ->groupBy('kode_barang')
    ->havingRaw('COUNT(*) > 1')
    ->orderByDesc('row_count')
    ->limit(20)
    ->get()
    ->map(fn ($row) => [
        'kode_barang' => $row->kode_barang,
        'row_count' => (int) $row->row_count,
    ])
    ->all();

$blankNamaBarangCount = (int) DB::table('products')->where('nama_barang', '')->count();
$blankMerekCount = (int) DB::table('products')->where('merek', '')->count();
$blankKodeBarangCount = (int) DB::table('products')->where('kode_barang', '')->count();

$sampleRows = DB::table('products')
    ->select('id', 'kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual', 'deleted_at')
    ->orderBy('id')
    ->limit(20)
    ->get()
    ->map(fn ($row) => [
        'id' => $row->id,
        'kode_barang' => $row->kode_barang,
        'nama_barang' => $row->nama_barang,
        'merek' => $row->merek,
        'ukuran' => $row->ukuran,
        'harga_jual' => $row->harga_jual,
        'deleted_at' => $row->deleted_at,
    ])
    ->all();

$report = [
    'generated_at' => date(DATE_ATOM),
    'database' => DB::connection()->getDatabaseName(),
    'total_products' => $totalProducts,
    'null_ukuran_count' => $nullUkuranCount,
    'non_null_ukuran_count' => $nonNullUkuranCount,
    'blank_nama_barang_count' => $blankNamaBarangCount,
    'blank_merek_count' => $blankMerekCount,
    'blank_kode_barang_count' => $blankKodeBarangCount,
    'duplicate_business_keys' => $duplicateBusinessKeys,
    'duplicate_kode_barang' => $duplicateKodeBarang,
    'sample_rows' => $sampleRows,
];

file_put_contents(
    __DIR__ . '/v2_products_preflight_scan_report.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

echo 'database=' . $report['database'] . PHP_EOL;
echo 'total_products=' . $report['total_products'] . PHP_EOL;
echo 'null_ukuran_count=' . $report['null_ukuran_count'] . PHP_EOL;
echo 'non_null_ukuran_count=' . $report['non_null_ukuran_count'] . PHP_EOL;
echo 'blank_nama_barang_count=' . $report['blank_nama_barang_count'] . PHP_EOL;
echo 'blank_merek_count=' . $report['blank_merek_count'] . PHP_EOL;
echo 'blank_kode_barang_count=' . $report['blank_kode_barang_count'] . PHP_EOL;
echo 'duplicate_business_keys_count=' . count($report['duplicate_business_keys']) . PHP_EOL;
echo 'duplicate_kode_barang_count=' . count($report['duplicate_kode_barang']) . PHP_EOL;
echo 'report=' . __DIR__ . '/v2_products_preflight_scan_report.json' . PHP_EOL;
