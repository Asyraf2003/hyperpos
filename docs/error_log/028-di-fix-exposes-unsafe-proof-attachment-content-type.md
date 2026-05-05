# 028 - Perbaikan DI mengekspos content type lampiran bukti pembayaran yang tidak aman

Status: dilaporkan
Keparahan: Medium
Klasifikasi: file error-log unik baru
Commit introduksi: c1baf1f
Status patch: belum disediakan pada laporan ini

## Ringkasan

Masalah keamanan menjadi dapat dieksploitasi setelah binding DI untuk `SupplierPaymentProofFileStoragePort` diperbaiki, sehingga jalur upload dan serve lampiran bukti pembayaran supplier menjadi reachable.

Refactor pada `storedFile()` dan `deleteMany()` sendiri bersifat mempertahankan perilaku. Perubahan yang relevan secara keamanan adalah binding yang benar membuat jalur yang menyimpan metadata MIME dari client menjadi aktif, lalu metadata itu dipakai lagi sebagai HTTP response `Content-Type` untuk response lampiran inline.

Controller upload memvalidasi tipe file yang diizinkan dengan rule file Laravel, tetapi menyimpan `UploadedFile::getClientMimeType()`. Nilai ini berasal dari metadata multipart request dan dapat dikendalikan uploader. Controller serve kemudian mengembalikan file tersimpan secara inline dan mengisi `Content-Type` dari nilai tersimpan tersebut.

Admin yang sudah login dapat mengupload payload yang byte-nya lolos validasi PDF/JPG/PNG, sementara multipart `Content-Type` dikirim sebagai `text/html`. Ketika admin lain membuka link lampiran bukti pembayaran, aplikasi dapat menyajikan lampiran tersebut secara inline sebagai HTML dari origin HyperPOS, sehingga stored XSS berjalan di sesi admin korban.

Nama file asli juga digabung langsung ke `Content-Disposition` tanpa konstruksi header yang aman.

## Kenapa ini file baru

Ini bukan masalah yang sama dengan laporan private storage/public helper exposure yang sudah ada. Storage tetap private, dan jalur eksploitnya adalah controller serve lampiran di aplikasi yang membutuhkan autentikasi.

Ini juga bukan masalah yang sama dengan temuan reflected XSS sebelumnya. Sink pada laporan ini adalah metadata lampiran tersimpan ditambah penyajian file inline dari origin aplikasi yang sama.

## File terdampak

- `app/Providers/HexagonalServiceProvider.php`
- `routes/web/admin_procurement.php`
- `app/Adapters/In/Http/Controllers/Admin/Procurement/AttachSupplierPaymentProofController.php`
- `app/Adapters/In/Http/Controllers/Admin/Procurement/ServeSupplierPaymentProofAttachmentController.php`
- `app/Adapters/Out/Procurement/LaravelSupplierPaymentProofFileStorageAdapter.php`

## Bukti

`HexagonalServiceProvider` melakukan binding `SupplierPaymentProofFileStoragePort` ke `LaravelSupplierPaymentProofFileStorageAdapter`, sehingga jalur storage file bukti pembayaran supplier dapat di-resolve.

`routes/web/admin_procurement.php` mengekspos route upload dan serve lampiran yang terdampak di bawah middleware `web`, `auth`, dan `admin.page`.

`AttachSupplierPaymentProofController` memvalidasi file bukti pembayaran sebagai `jpg`, `jpeg`, `png`, atau `pdf`, tetapi menyimpan `getClientOriginalName()` dan `getClientMimeType()` ke metadata file upload.

`LaravelSupplierPaymentProofFileStorageAdapter::storedFile()` mempertahankan `original_filename` dan `mime_type` dari metadata file yang diberikan tanpa menghitung ulang MIME di server dan tanpa allowlist.

`ServeSupplierPaymentProofAttachmentController` menyajikan lampiran secara inline secara default dan mengisi:

- `Content-Type` dari `$attachment->mimeType()`
- `Content-Disposition` dengan menggabungkan nama file asli ke string header

Tidak ada kontrol repository-level `X-Content-Type-Options: nosniff` atau CSP yang dilaporkan untuk response ini.

## Jalur serangan

Admin terautentikasi mengupload file bukti pembayaran supplier -> validasi upload menerima byte sebagai PDF/JPG/PNG yang diizinkan -> controller menyimpan metadata MIME multipart yang dikendalikan attacker -> adapter storage mempertahankan MIME tanpa perubahan -> admin korban membuka route lampiran bukti pembayaran -> controller serve mengembalikan response inline dengan `Content-Type` tersimpan -> browser memperlakukan response sebagai HTML/script same-origin -> stored XSS berjalan di sesi admin korban.

## Dampak

Eksploit yang berhasil dapat menjalankan JavaScript dari origin aplikasi HyperPOS di browser admin korban. Script tersebut dapat membaca halaman admin atau response API same-origin yang tersedia untuk korban, serta mengirim request state-changing same-origin sebagai korban.

Keparahan medium karena route membutuhkan admin yang sudah login dan eksploit membutuhkan admin attacker serta interaksi dari admin korban. Laporan ini tidak menunjukkan kompromi unauthenticated, RCE, kompromi deployment luas, atau dampak cross-tenant.

## Prasyarat

- Attacker memiliki akun admin yang sudah login.
- Attacker dapat mengupload file bukti pembayaran supplier.
- Byte file upload lolos validasi PDF/JPG/PNG.
- Attacker mengendalikan multipart `Content-Type`, misalnya `text/html`.
- Admin korban membuka route lampiran tanpa `download=true`.
- Tidak ada header server eksternal yang menimpa perilaku response tidak aman ini.

## Kontrol yang sudah ada

- Route dilindungi middleware `web`, `auth`, dan `admin.page`.
- Proteksi CSRF Laravel diharapkan berlaku untuk route POST web.
- Validasi upload membatasi file ke `jpg`, `jpeg`, `png`, dan `pdf`.
- Validasi upload membatasi file bukti pembayaran maksimal 3 file dan maksimal 2048 KB per file.
- File disimpan di private disk Laravel, bukan langsung di public storage.
- Cookie session Laravel diharapkan memakai HttpOnly dan SameSite sesuai default framework.

## Kontrol yang hilang

- MIME type tidak dihitung ulang di server sebelum disimpan.
- MIME type tersimpan tidak dibatasi ke allowlist aman sebelum disajikan.
- Penyajian inline diizinkan secara default.
- `X-Content-Type-Options: nosniff` tidak disetel oleh response lampiran.
- `Content-Disposition` dirangkai manual, bukan dibuat melalui helper framework yang aman.
- Nama file asli tidak di-escape dengan aman untuk konstruksi header pada jalur response yang ditunjukkan.

## Rekomendasi fix

Simpan MIME type hasil deteksi server, bukan `getClientMimeType()`.

Batasi nilai MIME yang disajikan ke allowlist aman seperti:

- `application/pdf`
- `image/jpeg`
- `image/png`

Untuk file yang tidak dikenal atau tidak cocok, sajikan sebagai `application/octet-stream` dan paksa download.

Tambahkan `X-Content-Type-Options: nosniff` pada response lampiran.

Gunakan helper Symfony/Laravel untuk membuat `Content-Disposition` secara aman, jangan menggabungkan string header secara manual.

Pertimbangkan untuk memaksa download pada lampiran bukti pembayaran, kecuali preview inline memang dibutuhkan dan dapat diisolasi dengan aman.

## Gap verifikasi

Sesi ini belum memverifikasi diff repository lokal atau perilaku runtime secara independen. Perlakukan entry ini sebagai berbasis laporan sampai `git status --short`, `git diff`, dan output test relevan disediakan.

Laporan menyatakan validasi mendemonstrasikan payload yang terlihat seperti PDF disimpan dengan `text/html` dan disajikan inline sebagai `text/html`, tetapi belum ada output test lokal yang diberikan pada sesi ini.
