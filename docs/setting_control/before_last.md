# Before Last — Kontrol Sebelum Keluar Halaman Kerja

Gunakan instruksi ini menjelang menutup halaman kerja.

## Tugas Anda sekarang
Susun ringkasan penutupan halaman kerja ini secara disiplin dan siap dipindahkan ke file handoff.

Jangan buka diskusi baru.
Jangan melebarkan scope.
Jangan memberi 3-5 cabang opsi baru kecuali memang ada blocker nyata.

## Struktur output yang wajib Anda hasilkan

### 1. `[FACT]` Fakta baru yang terkunci di halaman ini
Tuliskan hanya fakta yang benar-benar terbukti dari:
- file yang dibuat/diubah
- output command
- output test
- output audit
- output route/binding/runtime

### 2. `[DECISION]` Keputusan yang dikunci di halaman ini
Tuliskan keputusan nyata yang diambil, beserta alasan singkatnya.

### 3. `[FILES]` File yang dibuat/diubah
Pisahkan:
- file baru
- file ubah

### 4. `[PROOF]` Bukti verifikasi
Tuliskan bukti yang benar-benar ada, misalnya:
- syntax check
- audit
- test
- route list
- tinker binding
- make target

### 5. `[BLOCKER]` Penghambat yang masih ada
Kalau tidak ada blocker, tulis eksplisit: tidak ada blocker aktif.

### 6. `[NEXT]` Langkah berikut paling aman
Tuliskan satu langkah berikut paling aman.
Jangan lompat jauh.

### 7. `[PROGRESS]`
Tuliskan progres workflow saat keluar dari halaman ini.

### 8. Handoff ready summary
Susun ringkasan yang siap ditempel ke file handoff dengan isi:
- tujuan halaman kerja
- hasil minimum yang sudah tercapai
- state repo terakhir yang penting
- referensi dokumen yang relevan untuk langkah berikutnya

## Aturan tambahan
- kalau ada output yang gagal, tulis sebagai fakta gagal, jangan disamarkan
- kalau ada langkah yang belum selesai, sebutkan statusnya jelas
- jangan mengklaim step selesai 100% kalau bukti belum lengkap
- ringkasan harus siap dipakai halaman berikutnya tanpa membuka ulang sejarah panjang

## Tujuan file ini
Supaya setiap keluar halaman kerja selalu ada:
- state yang ringkas
- keputusan yang terlacak
- next step yang jelas
- handoff yang siap tempel
