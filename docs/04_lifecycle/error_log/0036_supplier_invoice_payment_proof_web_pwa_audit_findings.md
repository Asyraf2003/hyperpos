# 0036 - Supplier invoice payment proof Web/PWA audit findings

Status: Draft / Audit Finding / Not Patched
Keparahan: Medium
Klasifikasi: procurement payment proof / attachment hardening / UI contract mismatch / idempotency test gap

## Boundary Decision

HyperPOS current runtime untuk audit ini adalah Web/PWA.

Dasar boundary:

- `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md` menetapkan HyperPOS tidak lagi memakai Mobile API v1 sebagai runtime target.
- Audit baru wajib memperlakukan Blade/web route, middleware web, controller web, browser JavaScript, form submit, storage, projection, audit log, dan response redirect/session sebagai jalur utama.
- `routes/api.php`, `/api/v1/*`, Mobile API auth/payment proof, dan Kotlin Android adalah retired runtime dan tidak boleh dihidupkan ulang tanpa keputusan owner baru.

Evidence:

- `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:9`
- `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:11`
- `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:29`
- `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:31`
- Command audit awal membuktikan `routes/api.php` absent dengan output `OK: routes/api.php absent`.

## Active Scope

Audit jalur pembayaran faktur supplier / supplier invoice payment proof auto-lunas di jalur Web/PWA.

Target owner:

- UI Bayar = kirim bukti bayar.
- Tidak ada konsep cicil di UI utama.
- Tidak ada input nominal bayar di UI utama jika flow owner adalah auto-lunas.
- Setelah bukti bayar dikirim, sistem mencatat pembayaran sebesar outstanding/sisa tagihan dan invoice menjadi lunas.
- Legacy/manual/partial payment boleh ada sebagai histori/internal compatibility, tetapi tidak boleh muncul sebagai konsep utama di UI Bayar kecuali masih dipakai oleh flow lain dan dibuktikan.

## Finding A - Attachment storage path validation defense-in-depth

Severity: Medium

Type: Attachment path validation / defense-in-depth

Finding:

Attachment serving membaca `storage_path` dari database lalu mengambil file dari disk `local` tanpa whitelist prefix atau normalisasi path eksplisit pada read path. Upload normal memang menghasilkan path random di prefix `supplier-payment-proofs/{paymentId}/`, tetapi serving path masih bergantung pada nilai database.

Evidence:

- Attachment reader mengambil `storage_path` langsung dari tabel `supplier_payment_proof_attachments`: `app/Adapters/Out/Procurement/DatabaseSupplierPaymentProofAttachmentReaderAdapter.php:16`
- Handler membaca file memakai `$attachment->storagePath()`: `app/Application/Procurement/UseCases/GetSupplierPaymentProofAttachmentFileHandler.php:27`
- Storage adapter `get()` memakai path yang diterima untuk `Storage::disk('local')->get($path)`: `app/Adapters/Out/Procurement/LaravelSupplierPaymentProofFileStorageAdapter.php:73`
- Upload-generated directory diprefix `supplier-payment-proofs/`: `app/Adapters/Out/Procurement/LaravelSupplierPaymentProofFileStorageAdapter.php:84`
- Filename upload dirandomisasi dengan `bin2hex(random_bytes(16))`: `app/Adapters/Out/Procurement/LaravelSupplierPaymentProofFileStorageAdapter.php:97`
- Disk `local` memakai root private storage: `config/filesystems.php:33`

Impact:

Normal upload path relatif aman karena path dibuat server-side dan filename random. Namun jika ada DB corruption, seed/test utility yang salah, bug internal lain, atau future write path yang tidak memakai storage adapter, attachment endpoint dapat mencoba membaca path yang tidak berada di namespace bukti pembayaran supplier.

Patch recommendation:

- Tambahkan validasi path sebelum `exists()` dan `get()`.
- Allow hanya path relatif yang dimulai dengan `supplier-payment-proofs/`.
- Reject path kosong, absolute-like path, backslash, NUL byte, dan segmen `..`.
- Untuk invalid path, kembalikan `null` sehingga controller tetap menghasilkan `404`.

Test recommendation:

- Seed attachment dengan `storage_path` invalid seperti `../private.txt`, `/absolute/path`, `supplier-payment-proofs/../x.pdf`, dan path kosong.
- Assert route `admin.procurement.supplier-payment-proof-attachments.show` menghasilkan `404`.
- Pastikan valid path existing tetap bisa inline/download.

## Finding B - UI/backend contract mismatch for file format and max size

Severity: Medium

Type: UI/backend contract mismatch

Finding:

Kontrak format file dan ukuran maksimal tidak konsisten antara UI utama, halaman payment proofs, backend validation, dan safe MIME handling storage/serving.

Evidence:

- Index modal accept mencantumkan JPG/JPEG/PNG/WEBP/HEIC/HEIF/PDF dan MIME eksplisit: `resources/views/admin/procurement/supplier_invoices/index.blade.php:232`
- Payment proofs page accept mencantumkan `.jpg,.jpeg,.png,.pdf,image/*,application/pdf`: `resources/views/admin/procurement/supplier_invoices/payment_proofs.blade.php:53`
- Payment proofs page help text menulis maksimal 3 file, format JPG/JPEG/PNG/PDF, maksimal 2 MB per file: `resources/views/admin/procurement/supplier_invoices/payment_proofs.blade.php:57`
- Backend invoice-level upload menerima `image/jpeg,image/png,image/webp,image/heic,image/heif,application/pdf`: `app/Adapters/In/Http/Controllers/Admin/Procurement/UploadSupplierInvoicePaymentProofController.php:25`
- Backend invoice-level upload memakai `max:10240` dan pesan 10 MB: `app/Adapters/In/Http/Controllers/Admin/Procurement/UploadSupplierInvoicePaymentProofController.php:26`
- Storage safe MIME detector hanya mempertahankan `application/pdf`, `image/jpeg`, dan `image/png`, selain itu menjadi `application/octet-stream`: `app/Adapters/Out/Procurement/SupplierPaymentProofMimeTypeDetector.php:12`
- Attachment inline MIME whitelist juga hanya PDF/JPEG/PNG: `app/Adapters/In/Http/Controllers/Admin/Procurement/ServeSupplierPaymentProofAttachmentController.php:18`

Impact:

User bisa melihat UI yang menolak atau tidak menyebut format yang sebenarnya diterima backend, dan bisa melihat batas ukuran 2 MB padahal backend menerima 10 MB. Sebaliknya, `image/*` di halaman payment proofs bisa memberi sinyal bahwa semua image type diterima, padahal backend hanya menerima daftar MIME tertentu. Untuk WEBP/HEIC/HEIF, backend upload menerima file, tetapi storage/serving safe MIME dapat menurunkannya menjadi binary generic.

Patch recommendation:

- Owner perlu lock kontrak final format dan size terlebih dahulu.
- Setelah kontrak terkunci, samakan:
  - accept attribute index modal;
  - accept attribute payment proofs page;
  - help text;
  - backend validation MIME;
  - backend max size;
  - storage safe MIME detector;
  - attachment inline/download behavior.

Test recommendation:

- Tambahkan test Blade/UI contract yang membuktikan dua form payment proof menampilkan format dan max size yang sama dengan backend.
- Tambahkan test upload untuk setiap format yang owner setujui.
- Tambahkan test rejected format untuk format di luar kontrak.

## Finding C - UX/idempotency/concurrency test gap

Severity: Low

Type: UX/idempotency/concurrency test gap

Finding:

Server-side sudah memiliki guard penting: invoice dibaca dengan `lockForUpdate`, outstanding dihitung ulang pada submit, dan invoice yang sudah lunas ditolak. Namun UI belum terlihat men-disable submit button pada payment form, dan belum ada bukti test khusus untuk double click atau concurrent submit dua tab pada route invoice-level payment proof.

Evidence:

- JS membuat action modal dan membuka payment modal berdasarkan row payload: `public/assets/static/js/pages/admin-procurement-invoices-table.js:362`
- JS mengisi action URL dari row invoice id: `public/assets/static/js/pages/admin-procurement-invoices-table.js:300`
- JS hanya memiliki submit guard untuk void form, bukan payment proof form: `public/assets/static/js/pages/admin-procurement-invoices-table.js:587`
- Preflight mengambil invoice dengan `getByIdForUpdate`: `app/Application/Procurement/Services/SupplierPaymentProof/SupplierInvoicePaymentProofPreflight.php:23`
- DB adapter menerapkan `lockForUpdate()`: `app/Adapters/Out/Procurement/DatabaseSupplierInvoiceReaderAdapter.php:51`
- Outstanding dihitung ulang di preflight: `app/Application/Procurement/Services/SupplierPaymentProof/SupplierInvoicePaymentProofPreflight.php:37`
- Already-paid ditolak ketika outstanding kurang dari 1: `app/Application/Procurement/Services/SupplierPaymentProof/SupplierInvoicePaymentProofPreflight.php:40`
- Existing happy-path dan legacy partial tests ada: `tests/Feature/Procurement/UploadSupplierInvoicePaymentProofFeatureTest.php:20` dan `tests/Feature/Procurement/UploadSupplierInvoicePaymentProofFeatureTest.php:103`
- Belum ditemukan test khusus duplicate submit / concurrent submit untuk invoice-level payment proof route pada audit ini.

Impact:

Kemungkinan overpay secara domain terlihat ditekan oleh lock dan outstanding preflight. Namun UX double-click masih bisa menghasilkan second submit/failure yang membingungkan, dan behavior concurrent submit belum dikunci oleh regression test.

Patch recommendation:

- Tambahkan UI guard ringan: disable submit button pada submit pertama dan pertahankan native form behavior.
- Jangan jadikan UI guard sebagai satu-satunya kontrol; server lock tetap source of truth.
- Tambahkan duplicate/concurrency test untuk membuktikan hanya satu payment efektif yang tercatat.

Test recommendation:

- Test dua POST berurutan cepat ke route invoice-level payment proof: satu sukses, berikutnya gagal already-paid, total payment tidak melebihi grand total.
- Jika runtime test mendukung concurrency, tambahkan concurrent characterization test dua worker terhadap invoice yang sama.
- Assert projection tetap `paid`, outstanding `0`, dan payment sum tepat sebesar grand total.

## Safe Patch Order

1. Fix Finding A dulu: attachment path whitelist/normalization + invalid path test.
2. Fix Finding B setelah owner lock contract format/size.
3. Fix Finding C terakhir: double submit UI guard + duplicate/concurrency tests.

## Non-Goals

- Tidak menghidupkan kembali Mobile API.
- Tidak membuat `routes/api.php`.
- Tidak membuat `/api/v1` endpoint.
- Tidak menyentuh Kotlin/Android.
- Tidak menghapus histori audit lama hanya karena menyebut Mobile API.
- Tidak mengubah source app dalam log ini.

## Current Status

Audit finding sudah dicatat sebagai draft.

Belum ada patch source app.

Patch berikutnya harus dimulai dari Finding A setelah owner meminta eksekusi.
