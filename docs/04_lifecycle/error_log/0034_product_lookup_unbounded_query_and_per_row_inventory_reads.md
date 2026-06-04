# 034 - Product lookup fetches unbounded product rows and performs per-row inventory reads

Status: Reported
Keparahan: Medium
Klasifikasi: performance / scalability / lookup consistency

## Ringkasan

Product lookup untuk cashier dan mobile API mengambil hasil product search tanpa query-level limit, lalu membaca inventory per product row.

Mobile API memang membatasi response menjadi 20 row, tetapi limit diterapkan setelah product search mengembalikan array penuh. Web cashier lookup bahkan mengembalikan semua row yang lolos stock filter.

Ini bukan functional test failure pada katalog kecil. Ini adalah risiko performa saat katalog membesar.

## Bukti awal

Product reader search tidak menerima limit dan memakai `get()`:

`app/Adapters/Out/ProductCatalog/DatabaseProductReaderAdapter.php`

- `search(string $query): array`
- jika query kosong, return `findAll()`;
- jika query ada, menjalankan `applySearch(...)->get()` tanpa `limit`.

Lookup service hanya meneruskan search:

`app/Application/Note/Services/CashierNoteProductLookupData.php`

- `searchProducts(string $query)` return `$this->products->search(trim($query))`.

Cashier product lookup controller membaca inventory per product:

`app/Adapters/In/Http/Controllers/Cashier/Note/ProductLookupController.php`

- loop seluruh `$lookupData->searchProducts($query)`;
- pada setiap product memanggil `$lookupData->getInventoryByProductId($product->id())`;
- hanya product dengan stock > 0 yang masuk response;
- response mengembalikan semua row yang tersisa tanpa cap.

Mobile API handler membatasi setelah fetch:

`app/Application/MobileApi/Product/UseCases/SearchMobileApiProductsHandler.php`

- `array_slice($this->lookupData->searchProducts($normalizedQuery), 0, $limit)`;
- default limit `20`;
- inventory tetap dibaca per product pada rows hasil slice;
- query-level fetch tetap tidak terbatas sebelum slice.

Blueprint mobile sudah mencatat gap yang sama:

`docs/03_blueprints/mobile/0001_mobile_api.md`

- "Current result limit is applied in the application layer with `array_slice` at 20 rows."
- "Future improvement: query-level limit in `ProductReaderPort` or a dedicated mobile product reader for large catalogs."
- "Product search currently uses application-layer `array_slice` limit; query-level limit may be needed for large catalogs."

## Jalur bermasalah

User mengetik query product lookup -> adapter mengambil semua product yang cocok -> controller/handler melakukan inventory read per product -> response dibangun setelah filter stock -> pada katalog besar, request lookup dapat menjadi lambat dan membebani database.

## Dampak

- Latency lookup cashier meningkat.
- Mobile product search dapat tetap mahal meskipun response hanya 20 rows.
- Query count meningkat karena inventory dibaca per product.
- Risiko timeout atau beban database saat katalog dan inventory rows membesar.

Keparahan Medium karena issue ini belum menunjukkan data corruption atau security bypass, tetapi berdampak pada UX dan kapasitas runtime.

## Root cause

Contract product search saat ini mengembalikan array penuh dan tidak mendukung query-level limit.

Stock availability filter dilakukan di application loop dengan per-row inventory read, bukan dalam query lookup yang bisa join/aggregate secara bounded.

## Kontrol yang sudah ada

- Query kurang dari 2 karakter di web cashier lookup langsung return empty rows.
- Mobile API membatasi output menjadi 20 rows.
- Blueprint mobile sudah menandai query-level limit sebagai future improvement.

Kontrol tersebut belum cukup karena fetch product masih tidak bounded sebelum response limit diterapkan.

## Remediasi yang disarankan

Candidate patch direction:

- Tambahkan query-level limit pada product search contract atau buat dedicated lookup query.
- Terapkan limit sebelum hydration array penuh.
- Gabungkan inventory availability ke query lookup bila memungkinkan.
- Pastikan web cashier lookup juga punya cap response yang eksplisit.
- Tambahkan performance-oriented feature/unit test yang memastikan result cap dan query count tidak tumbuh linear secara tidak terkendali.

## Keputusan owner yang mungkin dibutuhkan

- Batas result web cashier lookup, misalnya 20, 30, atau 50 row.
- Apakah product reader port umum boleh berubah signature, atau dibuat dedicated lookup reader agar contract lama tidak ikut berubah.
- Apakah lookup harus hanya menampilkan product dengan stock > 0 untuk semua channel atau mobile boleh menampilkan stock 0.

## Verification gap

Belum ada patch.

Belum ada test query-level limit.

Belum ada query-count/performance proof untuk katalog besar.
