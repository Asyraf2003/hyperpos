# Note Detail Action Modal Contract

Tanggal: 2026-04-21

## Status
Locked for implementation.

## Tujuan
Mengunci kontrak UI detail nota kasir sebelum refactor tampilan dan JS launcher.

Kontrak ini dipakai untuk menghindari dua pola yang saling tumpang tindih:
- checklist permanen di tabel line
- aksi bayar/refund langsung tercampur dengan pemilihan line di halaman detail

## Keputusan yang dikunci

### 1. Halaman detail nota adalah halaman baca + launcher
Halaman `cashier.notes.show` diposisikan sebagai:
- ringkasan header nota
- ringkasan angka nota
- daftar line
- correction/history bila ada
- panel kanan ringan untuk launcher, bukan form panjang

### 2. Checklist hanya boleh hidup di dalam modal aksi
Checklist line **tidak** diletakkan permanen di tabel line.

Urutan interaksi yang dikunci:
1. user klik aksi terlebih dahulu
2. sistem membuka modal aksi yang sesuai
3. di dalam modal user memilih line yang eligible
4. user mengonfirmasi pembayaran atau refund

### 3. Tabel line tetap bersih
Tabel line tidak lagi menjadi tempat utama untuk:
- checkbox payment
- checkbox refund
- summary seleksi lintas line

Tabel line cukup menampilkan:
- identitas line
- tipe
- status line
- subtotal
- sudah dibayar
- sisa / refundable
- tombol aksi

### 4. Aksi line tetap ada sebagai launcher
Tombol yang diperbolehkan di row:
- `Bayar`
- `Refund`
- `Detail`

Makna tombol:
- `Bayar` membuka modal bayar dengan row yang diklik sebagai default selection awal
- `Refund` membuka modal refund dengan row yang diklik sebagai default selection awal
- user tetap boleh menambah atau mengurangi pilihan line di dalam modal

### 5. Modal bayar mengikuti keluarga workspace
Modal bayar harus mengikuti bahasa UI workspace existing:
- satu keluarga visual dengan create/edit workspace
- nominal selection dibaca dari line yang dipilih di dalam modal
- bila metode cash dipilih, kalkulator cash tetap dipakai

### 6. Modal refund mengikuti keluarga workspace
Modal refund harus mengikuti bahasa UI workspace existing:
- daftar line close yang eligible refund
- source histori pembayaran
- nominal refund
- alasan refund

### 7. Step implementasi dipisah dari step redesign besar
Pada tahap awal ini:
- backend entrypoint payment/refund existing tetap dipakai
- selected rows tetap dipertahankan sebagai contract HTTP sementara
- refactor UI dilakukan terlebih dahulu ke launcher + modal
- evolusi ke selected components dilakukan setelah launcher-modal stabil

## Payload minimum yang harus tersedia di detail page

### Payment action payload
- `can_show_payment_action`
- `payment_rows`
- `paymentModalConfig.action`
- `paymentModalConfig.date_default`
- `paymentModalConfig.selection_mode = modal_only`

### Refund action payload
- `can_show_refund_action`
- `refund_rows`
- `refund_payment_options`
- `refundModalConfig.action`
- `refundModalConfig.date_default`
- `refundModalConfig.selection_mode = modal_only`

## Scope implementasi terdekat

### Step 1
- lock contract
- prepare page data

### Step 2
- cleanup show page
- buang checkbox permanen di tabel
- pindah payment/refund ke modal launcher

### Step 3
- update JS launcher
- update feature tests view

### Step 4
- lanjut ke payment/refund selection vNext bila UI launcher sudah stabil

## Jangan dibuka ulang
- checklist permanen di tabel line
- panel pembayaran/refund panjang di sidebar detail nota
- cash calculator dihilangkan dari flow bayar cash
- evolusi ke component selection dikerjakan sebelum launcher-modal stabil

## Proof closure untuk Step 1
Step 1 dianggap selesai bila:
- kontrak ini sudah ada di repo
- page-data detail nota sudah menyiapkan payload launcher-modal
- flow lama masih kompatibel selama view belum dipindah total
