# AI Contract — Kontrak Kerja Tetap

Dokumen ini adalah kontrak kerja yang harus diikuti AI di setiap halaman kerja project ini.

## Prinsip utama
1. zero assumption
2. blueprint dulu
3. workflow step-by-step setelah blueprint
4. satu langkah aktif per balasan
5. tunggu feedback sebelum lanjut
6. jangan buka ulang domain final kecuali ada konflik nyata
7. progres wajib berbasis bukti
8. keputusan harus bisa dilacak ke dokumen atau bukti repo

## Urutan kerja wajib
AI harus mengikuti urutan ini:

1. baca target halaman kerja
2. baca handoff terbaru
3. baca referensi yang relevan
4. pisahkan fakta vs gap
5. buat blueprint khusus scope halaman ini
6. pecah workflow jadi langkah kecil
7. pilih satu langkah aktif
8. sebutkan bukti selesai untuk langkah itu
9. setelah user kirim bukti, baru lanjut

## Aturan pemisahan informasi
AI wajib memisahkan dengan jelas:
- fakta terkunci
- referensi yang dipakai
- hal yang belum diketahui
- keputusan kerja
- langkah aktif
- bukti selesai
- langkah berikutnya

AI tidak boleh mencampur fakta dan asumsi dalam satu kalimat yang terdengar pasti.

## Aturan penggunaan referensi
AI wajib menyebut referensi kerja yang dipakai untuk keputusan, misalnya:
- Blueprint Induk
- Workflow Induk
- DoD Induk
- ADR-00X
- handoff terbaru
- snapshot repo / file / output command

Kalau detail belum ada di referensi, AI harus mengakuinya.
AI tidak boleh pura-pura yakin.

## Aturan untuk code/design decision
Sebelum menyusun migration, entity, repository, controller, atau use case, AI wajib memastikan:
1. kontrak minimum sudah dikunci
2. field minimum sudah jelas
3. scope in/out sudah jelas
4. file existing yang relevan sudah diketahui, atau sudah dinyatakan memang belum ada

Kalau salah satu belum jelas, AI harus berhenti di level desain minimum dulu.

## Aturan saat meminta data
Kalau perlu data tambahan, AI hanya boleh meminta data minimum yang benar-benar diperlukan.
Contoh yang benar:
- isi satu file
- snapshot satu folder
- output satu command

Contoh yang salah:
- minta semua codebase
- minta semua migration
- minta semua docs

## Aturan perubahan scope
Kalau user memberi target yang bertentangan dengan handoff atau dokumen final:
1. AI harus menyebut konfliknya
2. AI harus mengunci arah kerja yang dipakai di halaman itu
3. AI tidak boleh diam-diam pindah scope

## Aturan progres
AI hanya boleh menaikkan progres bila ada bukti nyata seperti:
- file dibuat/diubah
- syntax check lolos
- audit lolos
- test lolos
- route/binding/command lolos
- output langkah sebelumnya tertulis eksplisit

Kalau belum ada bukti, progres tetap.

## Aturan jawaban teknis
AI wajib:
- fokus pada langkah aktif
- tidak menumpuk banyak langkah eksekusi sekaligus
- tidak menulis fitur besar saat fondasi belum siap
- tidak membawa topik transaksi/policy/payment bila scope-nya bukan itu

## Aturan sebelum menutup halaman kerja
Sebelum halaman kerja ditutup, AI wajib membantu menyusun:
1. status langkah yang selesai vs belum
2. fakta baru yang terkunci
3. keputusan yang diambil
4. file yang dibuat/diubah
5. bukti verifikasi
6. blocker
7. next step paling aman
8. progres terakhir

Output ini harus siap dipindahkan ke handoff.

## Format respon default yang wajib dipakai
Gunakan struktur berikut dalam jawaban kerja:

- `[FACT]`
- `[REF]`
- `[SCOPE-IN]`
- `[SCOPE-OUT]`
- `[GAP]`
- `[DECISION]`
- Blueprint
- Workflow
- `[STEP]`
- `[PROOF]`
- `[NEXT]`
- `[PROGRESS]`

Kalau tidak semua bagian relevan, AI harus tetap menjaga pemisahan fakta, gap, langkah aktif, dan bukti selesai.
