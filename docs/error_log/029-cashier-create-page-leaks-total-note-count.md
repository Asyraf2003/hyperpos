# 029 - Halaman create kasir membocorkan total jumlah nota

Status: dilaporkan
Keparahan: Medium
Klasifikasi: file error-log unik baru
Commit introduksi: 69cf998
Status patch: belum disediakan pada laporan ini

## Ringkasan

Halaman create transaction workspace kasir membocorkan total jumlah nota global melalui default customer name yang dihasilkan.

`CreateTransactionWorkspacePageController` memanggil `CreateTransactionWorkspacePageDataBuilder::build()` dan membaca `defaultCustomerName`. Builder membuat nilai tersebut sebagai `Pelanggan no ` ditambah `NoteReaderPort::countAll() + 1`.

Adapter note reader produksi mengimplementasikan `countAll()` sebagai hitung tanpa scope atas seluruh tabel `notes`. Nilai itu kemudian dirender ke halaman create workspace yang terlihat oleh kasir sebagai default customer name atau placeholder, dan juga tersedia melalui data konfigurasi halaman.

Browsing nota kasir di tempat lain dibatasi oleh window tanggal. Karena itu, mengekspos total jumlah nota global melewati batas visibilitas kasir yang seharusnya dan membocorkan metadata volume bisnis.

## Kenapa ini file baru

Ini bukan masalah yang sama dengan laporan historical closed note disclosure. Laporan tersebut mengekspos baris nota historis melalui perilaku browsing yang dapat diakses kasir.

Masalah ini mengekspos volume nota global berbentuk aggregate melalui default customer label di halaman create kasir. Nilai yang bocor bukan baris nota, tetapi tetap membocorkan metadata volume bisnis di luar visibilitas date-windowed normal kasir.

## File terdampak

- `app/Adapters/In/Http/Controllers/Cashier/Note/CreateTransactionWorkspacePageController.php`
- `app/Application/Note/Services/CreateTransactionWorkspacePageDataBuilder.php`
- `app/Adapters/Out/Note/DatabaseNoteReaderAdapter.php`
- `app/Adapters/Out/Note/Queries/CashierNoteHistoryBaseQuery.php`
- `resources/views/cashier/notes/workspace/partials/info-card.blade.php`

## Bukti

`CreateTransactionWorkspacePageController` memanggil page data builder dan memakai `defaultCustomerName` ketika tidak ada old input atau draft customer name yang menimpa nilai default.

`CreateTransactionWorkspacePageDataBuilder::build()` membuat:

- `defaultCustomerName` = `Pelanggan no ` ditambah `NoteReaderPort::countAll() + 1`

`DatabaseNoteReaderAdapter::countAll()` menjalankan count tanpa scope atas seluruh tabel `notes`.

`CashierNoteHistoryBaseQuery` membatasi visibilitas history kasir ke window tanggal terpilih, sehingga count lifetime tanpa scope lebih luas daripada visibilitas nota kasir biasa.

Blade workspace info card merender default customer name yang berasal dari count tersebut ke halaman create yang terlihat oleh kasir.

## Jalur serangan

Sesi kasir terautentikasi -> buka create transaction workspace -> controller memanggil page data builder -> builder memanggil count nota tanpa scope -> adapter database menghitung semua nota -> halaman merender `Pelanggan no {global_count + 1}` -> kasir dapat menyimpulkan total lifetime nota atau metadata volume bisnis.

## Dampak

Kasir dapat menyimpulkan jumlah global nota atau transaksi, termasuk record di luar window tanggal normal kasir.

Dampaknya medium karena ini membocorkan metadata aggregate volume bisnis, tetapi tidak membocorkan isi lengkap nota, PII pelanggan, kredensial, detail pembayaran, data inventory, atau kemampuan write.

## Prasyarat

- Aplikasi web Laravel menyajikan route cashier note workspace.
- Actor memiliki sesi terautentikasi dengan akses kasir.
- Actor dapat mengakses create transaction workspace.
- Tidak ada old input atau draft customer name yang menimpa nilai default.
- Total jumlah nota global dianggap metadata bisnis sensitif yang tidak dimaksudkan terlihat oleh semua kasir.

## Kontrol yang sudah ada

- Route membutuhkan autentikasi session Laravel.
- Middleware cashier area access berlaku.
- Middleware transaction entry berlaku.
- Query history kasir dibatasi window tanggal.
- Escaping output Blade mengurangi risiko script injection, tetapi tidak mencegah disclosure metadata.

## Kontrol yang hilang

- `countAll()` tidak memiliki scope sesuai visibilitas kasir.
- Default customer name bergantung pada volume tabel nota global.
- Halaman create mengekspos aggregate global yang lebih luas daripada scope history kasir.
- Tidak ada sumber sequence non-sensitif terpisah untuk placeholder label yang terlihat oleh kasir.

## Rekomendasi fix

Jangan menurunkan default customer name yang terlihat oleh kasir dari total jumlah nota global tanpa scope.

Gunakan salah satu pendekatan yang lebih aman:

1. Pakai placeholder netral seperti `Pelanggan baru`.
2. Pakai label sementara berbasis sesi atau draft yang tidak terkait dengan count global persistent.
3. Pakai counter yang di-scope per kasir atau per hari jika memang dibutuhkan operasional.
4. Generate nomor nota final hanya setelah nota dibuat, dan tampilkan hanya kepada actor yang memang boleh melihat nota tersebut.

Jika count memang dibutuhkan, expose hanya count yang sesuai dengan scope visibilitas kasir yang diizinkan.

## Gap verifikasi

Sesi ini belum memverifikasi diff repository lokal atau perilaku runtime secara independen. Perlakukan entry ini sebagai berbasis laporan sampai `git status --short`, `git diff`, dan output test relevan disediakan.

Laporan menyatakan full Laravel HTTP execution tidak dilakukan karena dependencies/vendor tidak tersedia. Artinya runtime HTTP coverage belum terbukti pada sesi ini.
