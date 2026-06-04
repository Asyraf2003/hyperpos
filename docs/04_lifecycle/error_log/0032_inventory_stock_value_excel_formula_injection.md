# 032 - Inventory stock value Excel export writes product text through formula-capable cells

Status: Reported
Keparahan: High
Klasifikasi: report export security / spreadsheet formula injection

## Ringkasan

Inventory stock value Excel export menulis beberapa field teks produk menggunakan `setCellValue()` langsung. Pada PhpSpreadsheet, string yang diawali formula marker seperti `=1+1` dapat disimpan sebagai formula cell, bukan literal string.

Repository sudah memiliki pola aman di report writer lain, yaitu memakai `setCellValueExplicit(..., DataType::TYPE_STRING)` untuk string. Supplier payable export juga sudah memiliki test yang memastikan formula-like text tetap literal string.

Gap ini membuat export inventory stock value tidak seragam dengan hardening Excel export lain.

## Bukti awal

Writer inventory snapshot menulis field produk dengan `setCellValue()`:

`app/Application/Reporting/Exports/InventoryStockValueReportExcelSnapshotSheetWriter.php`

- `product_id`
- `kode_barang`
- `nama_barang`
- `merek`
- `ukuran`

Writer inventory movement juga menulis field produk dengan `setCellValue()`:

`app/Application/Reporting/Exports/InventoryStockValueReportExcelMovementSheetWriter.php`

- `product_id`
- `kode_barang`
- `nama_barang`

Proof PhpSpreadsheet lokal:

`php -r 'require "vendor/autoload.php"; $s = new PhpOffice\PhpSpreadsheet\Spreadsheet(); $sheet = $s->getActiveSheet(); $sheet->setCellValue("A1", "=1+1"); echo $sheet->getCell("A1")->getDataType(), PHP_EOL, $sheet->getCell("A1")->getValue(), PHP_EOL;'`

Output:

- `f`
- `=1+1`

Kesimpulan: `setCellValue()` dapat menyimpan formula-like string sebagai formula cell.

Pola aman yang sudah ada:

`app/Application/Reporting/Exports/TransactionReportExcelTableWriter.php`

- jika value string, writer memakai `setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING)`.

Test aman yang sudah ada:

`tests/Feature/ReportingExports/SupplierPayableReportExcelExportFeatureTest.php`

- `test_supplier_payable_excel_export_writes_formula_like_text_as_literal_strings()` menanam nilai `=1+1` dan `=2+2`;
- test memastikan cell value tetap string dan `getDataType()` adalah `TYPE_STRING`.

## Jalur rentan

User yang dapat membuat atau mengubah master produk memasukkan nilai formula-like pada kode/nama/merek produk -> admin mengekspor inventory stock value report ke Excel -> workbook berisi formula cell dari data produk -> saat workbook dibuka di spreadsheet client, formula dapat dievaluasi sesuai perilaku aplikasi spreadsheet.

## Dampak

Dampak bergantung pada aplikasi spreadsheet dan security setting client. Risiko yang relevan:

- formula injection di file XLSX hasil aplikasi;
- kemungkinan exfiltration atau command-like behavior pada spreadsheet client tertentu;
- trust boundary report export menjadi tidak aman untuk data teks yang berasal dari user/master data.

Keparahan dibaca High karena ini adalah unsafe output di file yang bisa dibuka admin, dan field produk dapat berasal dari input aplikasi.

## Root cause

Inventory stock value export tidak memakai central safe string writer atau helper `setCellValueExplicit(..., TYPE_STRING)` untuk string fields.

Hardening yang sudah diterapkan di supplier payable export belum diterapkan secara seragam ke inventory stock value export.

## Kontrol yang sudah ada

- Supplier payable Excel export sudah punya test formula injection.
- Central report table writer sudah punya pola safe-string.
- Numeric columns di inventory writer memakai casting integer untuk nilai uang/qty.

Kontrol tersebut tidak melindungi inventory stock value writer karena writer ini langsung menggunakan `setCellValue()` pada kolom teks.

## Remediasi yang disarankan

Candidate patch direction:

- Pakai `setCellValueExplicit(..., DataType::TYPE_STRING)` untuk semua kolom teks inventory stock value export.
- Pertimbangkan extracted helper agar semua Excel writer memakai boundary string-safe yang sama.
- Tambahkan feature test inventory export dengan product code/name/brand formula-like.
- Assert `getDataType()` untuk field produk adalah `TYPE_STRING`.

## Verification gap

Belum ada patch.

Belum ada test inventory stock value export yang membuktikan formula-like product text ditulis sebagai literal string.

Belum ada scan menyeluruh semua Excel writer untuk memastikan tidak ada writer lain yang masih memakai `setCellValue()` pada user-controlled strings.
