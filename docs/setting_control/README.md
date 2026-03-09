# Setting Control README

Folder ini dipakai untuk menjaga setiap halaman kerja tetap seragam, hemat token, dan tidak liar arah diskusinya.

## Tujuan
1. memastikan halaman kerja baru selalu masuk dengan konteks minimum yang benar
2. memastikan AI bekerja dengan kontrak yang sama di setiap halaman
3. memastikan sebelum keluar selalu ada handoff yang rapi dan bisa dilanjutkan
4. memastikan keputusan, bukti, dan progres punya format yang konsisten

## File dan fungsi
- `docs/setting_control/first_in.md`
  - dipakai saat MASUK ke halaman kerja baru
  - memaksa AI membaca konteks minimum yang wajib
  - memaksa AI memisahkan fakta, scope, gap, dan langkah aktif

- `docs/setting_control/ai_contract.md`
  - dipakai sebagai kontrak kerja tetap
  - memaksa AI mengikuti urutan: blueprint -> workflow -> step aktif
  - memaksa zero assumption
  - memaksa pemisahan fakta vs usulan

- `docs/setting_control/before_last.md`
  - dipakai MENJELANG keluar dari halaman kerja
  - memaksa AI merangkum hasil, bukti, progres, blocker, dan next step
  - output dari file ini menjadi dasar isi handoff

- `docs/handoff/handoff_template.md`
  - template handoff standar
  - dipakai untuk menyimpan state kerja terakhir
  - file hasil nyata nanti disimpan ke `docs/handoff/`

## Urutan pakai saat masuk halaman kerja baru
Pakai urutan ini agar tidak campur aduk:

1. tempel `docs/setting_control/first_in.md`
2. tempel handoff terbaru dari `docs/handoff/`
3. tempel `docs/setting_control/ai_contract.md`
4. tempel hanya dokumen referensi yang relevan:
   - `docs/blueprint/blueprint_v1.md`
   - `docs/workflow/workflow_v1.md`
   - `docs/dod/dod_v1.md`
   - ADR yang relevan dari `docs/adr/`
5. baru beri target halaman kerja itu

## Data minimum yang wajib dibawa saat masuk halaman kerja
Jangan bawa semua dokumen kalau tidak relevan. Bawa minimum berikut:

### Wajib
- tujuan halaman kerja saat ini
- handoff terbaru
- step workflow aktif
- batas scope in / out
- DoD relevan
- ADR relevan
- daftar file/folder yang sudah dibuat atau disentuh
- bukti terakhir:
  - output test
  - output audit
  - route/command/check yang sudah lolos

### Opsional tapi dianjurkan bila relevan
- snapshot tree folder yang relevan
- isi file yang akan diubah
- migration/model/use case yang terkait langsung
- error log terbaru bila ada kegagalan

## Aturan hemat token
- jangan tempel semua docs sekaligus
- tempel hanya ADR yang relevan
- tempel hanya file code yang akan disentuh
- kalau handoff sudah lengkap, jangan ulang semua narasi lama
- minta AI menulis:
  - fakta terkunci
  - hal yang belum terkunci
  - langkah aktif berikutnya
  - bukti yang diperlukan
- satu langkah aktif per balasan

## Kode struktur keputusan yang wajib dipakai
Gunakan label ini agar seragam di semua halaman:

- `[FACT]` fakta yang sudah terbukti / terkunci
- `[REF]` referensi dokumen yang dipakai
- `[SCOPE-IN]` yang masuk scope
- `[SCOPE-OUT]` yang di luar scope
- `[GAP]` data/fakta yang belum ada
- `[DECISION]` keputusan yang sudah dikunci
- `[STEP]` langkah aktif sekarang
- `[PROOF]` bukti yang harus ada
- `[BLOCKER]` penghambat nyata
- `[NEXT]` langkah berikutnya
- `[PROGRESS]` progres persen workflow

## Aturan validasi progres
Persen progres hanya boleh naik bila ada bukti nyata, misalnya:
- file berhasil dibuat/diubah
- syntax check lolos
- audit lolos
- test lolos
- binding/route/command lolos
- keputusan langkah sebelumnya sudah tertulis eksplisit

Kalau belum ada bukti, progres tidak boleh naik.

## Aturan keluar halaman kerja
Sebelum menutup halaman kerja, selalu:
1. tempel `docs/setting_control/before_last.md`
2. minta AI menyusun ringkasan akhir
3. simpan hasil ringkasan ke file handoff baru berdasarkan `docs/handoff/handoff_template.md`

## Naming handoff yang disarankan
Gunakan format yang konsisten, misalnya:

- `docs/handoff/handoff_step_2.md`
- `docs/handoff/handoff_step_3_product_master.md`
- `docs/handoff/handoff_2026-03-09_product_master_slice_1.md`

Pilih satu pola dan konsisten.

## Prinsip utama
- jangan lanjut ke langkah berikutnya bila output langkah sekarang belum tertulis jelas
- jangan izinkan AI membuat migration/entity/use case dari asumsi
- jangan buka ulang diskusi domain final kecuali ada konflik nyata
- selalu pakai handoff sebagai source of truth halaman kerja terakhir
