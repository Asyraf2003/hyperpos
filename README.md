# Kasir V2

> Fondasi operasional untuk web admin kasir yang stabil, terukur, dan siap berkembang.
>
> Fokus utama project ini adalah **stok**, **barang**, **suplai**, **transaksi**, dan **laporan** — dengan domain inti yang sengaja dikunci sejak awal agar fase berikutnya seperti **Telegram bot** dan **PDF** bisa ditambahkan **tanpa membongkar ulang core domain**.

---

## Apa project ini?

**Kasir V2** adalah project web admin untuk operasi kasir dan backoffice yang dibangun dengan pendekatan **domain-first** dan **hexagonal architecture**.

Project ini tidak diposisikan sebagai UI cepat jadi lalu ditambal belakangan. Sebaliknya, project ini dibangun dengan urutan:

1. **istilah domain dikunci**
2. **aturan bisnis dikunci**
3. **alur transaksi dikunci**
4. **source of truth data dikunci**
5. baru setelah itu **fitur, halaman, dan ekspansi** dikembangkan di atas fondasi yang sudah stabil

Hasil yang dituju adalah sistem yang:

- jelas dari sisi istilah bisnis
- tahan terhadap perubahan fitur
- aman untuk perhitungan stok dan transaksi
- mudah diaudit
- tidak membuat laporan “menebak-nebak” data dari banyak arah

---

## Masalah yang diselesaikan

Banyak sistem kasir gagal karena sejak awal tidak tegas pada hal-hal berikut:

- stok punya lebih dari satu sumber kebenaran
- istilah bisnis berubah-ubah antara UI, database, dan code
- transaksi pelanggan, pembayaran, dan refund bercampur
- laporan dibangun duluan sebelum domain inti benar-benar rapi
- penambahan kanal baru seperti bot atau PDF memaksa bongkar ulang flow lama

Kasir V2 dibangun untuk menghindari pola itu.

Project ini menempatkan **domain inti sebagai kontrak utama**, sehingga:

- stok selalu mengikuti engine inventory resmi
- transaksi pelanggan punya lifecycle yang eksplisit
- suplai menjadi basis masuk stok dan biaya
- laporan hanya membaca domain final, bukan menyusun ulang logika bisnis sendiri

---

## Tujuan utama

Target akhir project ini adalah menyediakan **fondasi operasional web admin** yang stabil untuk:

- master barang
- stok dan pergerakan stok
- suplai / pembelian masuk
- transaksi pelanggan
- pembayaran parsial
- refund
- laporan berbasis domain final

Setelah fondasi tersebut stabil, project diarahkan agar memudahkan ekspansi ke:

- integrasi Telegram bot
- output PDF
- kanal operasional tambahan lain tanpa mengubah core domain

---

## Filosofi project

## 1) Domain dulu, UI mengikuti

UI tidak boleh memaksa domain.
Istilah yang tampil ke user harus berasal dari keputusan domain yang sudah dikunci.

## 2) Satu sumber kebenaran per masalah

Untuk setiap area penting, project ini menetapkan source of truth yang jelas.

Contoh:

- stok tidak dihitung dari tebakan halaman
- mutasi stok tidak dilakukan langsung dari UI
- laporan tidak menciptakan ulang logika transaksi

## 3) Hexagonal architecture, bukan controller-centric app

Controller hanya menjadi pintu masuk.
Aturan bisnis hidup di domain dan application layer.
Akses database, persistence, dan integrasi keluar diletakkan di adapter.

## 4) Perubahan harus bisa diaudit

Perubahan penting harus bisa ditelusuri:
- keputusan
- alur workflow
- bukti verifikasi
- perubahan file
- hasil test

## 5) Bukti lebih penting dari opini

Progress tidak dihitung dari niat.
Progress hanya diakui bila ada bukti:
- file nyata
- command nyata
- hasil test nyata
- perilaku sistem yang terverifikasi

## 6) Build untuk bertahan, bukan sekadar selesai

Project ini sengaja menahan diri dari shortcut yang merusak masa depan:
- domain tidak dibuka ulang tanpa konflik nyata
- public contract dijaga stabil
- boundary antar layer dijaga
- DoD dipakai sebagai pagar kualitas minimum

---

## Konsep domain inti

Agar semua orang yang membaca README ini langsung paham struktur bisnis project, berikut kontrak domain final yang sudah dikunci.

### Products
**`products`** adalah **master barang**.

Fungsi utamanya:
- identitas barang
- data dasar barang
- titik referensi untuk transaksi dan suplai

### Inventory
**`product_inventory`** dan **`inventory_movements`** adalah **source of truth stok**.

Artinya:
- stok resmi dibaca dari domain inventory
- pengurangan / penambahan stok harus lewat inventory engine resmi
- halaman atau fitur lain tidak boleh punya logika stok paralel

### Supply / biaya
**`supplier_invoices`** beserta item-itemnya adalah **pintu masuk stok** dan **dasar biaya**.

Perannya:
- menerima barang masuk
- menjadi basis perhitungan **avg_cost / COGS**
- menghubungkan suplai ke stok secara tertib

### Customer sales domain
Dalam bahasa UI final:

- **`customer_orders`** = **Nota Pelanggan**
- **`customer_transactions`** = **Kasus**
- **`customer_transaction_lines`** = **Rincian**

Pemisahan ini penting agar:
- dokumen transaksi pelanggan tetap jelas
- satu pelanggan bisa punya konteks transaksi yang rapi
- detail item tidak bercampur dengan identitas dokumen utama

### Reports
Laporan hanya **membaca domain final**.

Itu berarti laporan:
- bukan tempat menciptakan aturan bisnis baru
- bukan tempat “memperbaiki” inkonsistensi domain
- harus menjadi pembaca yang disiplin atas source of truth yang sudah ada

---

## Istilah UI yang dikunci

Agar tidak ada kebingungan antara bahasa bisnis, UI, dan implementasi, project ini mengunci istilah final berikut:

- **Nota** = dokumen pelanggan
- **Kasus** = konteks transaksi pelanggan
- **Rincian** = detail item / detail transaksi

Status UI final yang dipakai:

- `draft` = **Belum Lunas**
- `paid` = **Lunas**
- `canceled` = **Batal**
- `refunded` = **Refund**

Aturan penting yang dikunci:

- delete hanya boleh untuk **draft** dan hanya bila tidak menimbulkan konsekuensi domain
- transaksi **paid** tidak boleh di-cancel
- transaksi **paid** yang perlu dibalik harus lewat **refund**
- target model pembayaran final adalah **partial payment eksplisit**

---

## Alur bisnis inti

Secara garis besar, alur bisnis project ini adalah sebagai berikut.

### 1) Barang didefinisikan di master
Barang hidup sebagai `products`.

### 2) Suplai masuk menambah stok dan dasar biaya
Barang masuk melalui domain suplai:
- tercatat sebagai supplier invoice
- item suplai menjadi dasar biaya
- stok masuk mengikuti inventory engine resmi

### 3) Transaksi pelanggan mengonsumsi domain final
Saat transaksi pelanggan terjadi:
- sistem bekerja dengan istilah Nota / Kasus / Rincian
- item yang menggunakan stok toko harus memotong stok lewat inventory engine resmi
- item service dan item pembelian eksternal diperlakukan sesuai aturan domain masing-masing

### 4) Pembayaran diproses dengan lifecycle eksplisit
Pembayaran tidak disederhanakan secara palsu.
Partial payment diperlakukan sebagai warga kelas satu.

### 5) Refund adalah lifecycle terpisah
Refund bukan cancel yang disamarkan.
Refund adalah langkah domain yang eksplisit setelah transaksi tertentu sudah berjalan.

### 6) Laporan membaca hasil akhir domain
Laporan dibangun di atas domain yang sudah rapih, bukan sebaliknya.

---

## Aturan perilaku domain yang penting

Berikut beberapa perilaku domain yang menjadi karakter inti project ini:

- external purchase **tidak masuk inventory**
- store stock sale only harus memotong stok resmi
- service dengan komponen stok toko harus memotong stok resmi
- insufficient stock harus ditolak
- floor pricing untuk store stock harus dijaga
- total note / transaksi harus dihitung dari engine domain, bukan dari UI
- mutation transaksi harus mengikuti controller → use case sebagai source of truth

---

## Tanggung jawab per layer

Project ini menggunakan pola tanggung jawab yang tegas.

### Domain / Core
Bertanggung jawab atas:

- istilah dan model bisnis inti
- invariant
- policy
- lifecycle status
- validasi aturan bisnis
- kalkulasi yang benar secara domain

Domain tidak boleh bergantung pada detail framework atau database.

### Application / Use Case
Bertanggung jawab atas:

- orkestrasi alur
- koordinasi port
- urutan proses bisnis
- menjaga agar satu use case melakukan satu pekerjaan yang jelas

Layer ini menghubungkan kebutuhan user dengan aturan domain.

### Adapters In
Bertanggung jawab atas:

- menerima request
- validasi input masuk
- menerjemahkan input ke use case
- mengembalikan response ke HTTP/UI

Controller bukan tempat aturan bisnis utama.

### Adapters Out
Bertanggung jawab atas:

- database
- query
- persistence
- integrasi ke luar
- implementasi port

Layer ini boleh tahu detail Laravel, database, dan storage.
Domain tidak.

### UI / View
Bertanggung jawab atas:

- representasi status
- alur input user
- progressive enhancement
- menampilkan data dengan istilah domain yang sudah benar

UI tidak boleh menciptakan aturan stok, pembayaran, atau refund sendiri.

---

## Prinsip implementasi UI

Beberapa prinsip UI yang sudah dipakai di project ini:

- pendekatan **kasir-first**
- shared layout base
- native JavaScript dengan **progressive enhancement**
- fallback submit form tetap harus hidup
- formatting rupiah mengikuti format ribuan seperti `15.000`
- endpoint seperti `/cashier/products/search` harus mendukung:
  - page biasa
  - fetch JavaScript
- semua mutasi transaksi tetap lewat **controller → use case** sebagai source of truth

---

## Proses kerja project

Project ini mengikuti pola kerja yang disiplin.

### 1) Blueprint-first
Setiap pengembangan harus berdiri di atas blueprint.
Fitur tidak boleh langsung melompat ke implementasi tanpa posisi yang jelas dalam arah project.

### 2) Workflow step-by-step
Pekerjaan dijalankan per step yang jelas.
Satu step ditutup dulu dengan bukti sebelum lanjut ke step berikutnya.

### 3) Zero assumption
Setiap langkah harus punya:
- fakta
- kondisi saat ini
- tujuan langkah

Artinya keputusan tidak boleh dibuat dari tebakan.

### 4) Evidence-based progress
Progress baru sah bila ada bukti:
- syntax check
- automated test
- audit / arch test
- output command
- file yang benar-benar berubah

### 5) Handoff-friendly
Setiap slice kerja harus bisa ditutup dengan handoff yang cukup kuat sehingga halaman / sesi berikutnya bisa lanjut tanpa kehilangan konteks.

---

## Definition of Done (DoD)

Sebuah perubahan dianggap selesai hanya bila memenuhi pagar kualitas minimum berikut.

### DoD inti
- solusi mengikuti blueprint dan workflow aktif
- scope in / scope out jelas
- keputusan tidak melanggar domain yang sudah dikunci
- file yang diubah punya alasan yang jelas
- perubahan dapat dijelaskan dari sisi domain, bukan hanya teknis

### DoD verifikasi
Minimal ada bukti dalam bentuk yang relevan, seperti:

- syntax check file yang diubah
- feature test
- unit test
- arch / audit test
- sanity check flow yang disentuh

Contoh command yang lazim dipakai di repo ini:

~~~bash
php -l <file>
php artisan test
~~~

Bila slice menyentuh area tertentu, maka verifikasi harus relevan dengan area itu.
Contoh:
- perubahan HTTP → perlu feature test HTTP
- perubahan boundary / dependency → perlu arch atau audit test
- perubahan domain calculation → perlu unit / feature test domain terkait

### DoD kebersihan arsitektur
- boundary hexagonal tetap terjaga
- public contract yang dilindungi tidak rusak
- source of truth tidak bercabang
- tidak ada shortcut yang memindahkan aturan bisnis ke controller atau view
- keputusan baru yang memengaruhi arah project harus tercermin pada dokumen kerja / handoff

---

## Guardrails arsitektur

Project ini menjaga beberapa guardrail penting.

### Stable public contracts
Kontrak publik yang sudah dipakai lintas layer tidak boleh diubah sembarangan.

### Hexagonal boundaries
Import dan dependensi harus mengikuti boundary yang benar.

### Controller tipis
Controller harus tetap tipis.
Business rule bukan milik controller.

### Error handling yang aman
Error harus diarahkan ke mekanisme penanganan yang konsisten dan tidak membocorkan detail mentah.

### Debug route harus terkendali
Rute debug tidak boleh terbuka tanpa kontrol yang jelas.

### Satu folder, satu tanggung jawab yang masuk akal
Kebersihan file dan struktur dijaga agar repo tetap bisa diaudit dan dirawat.

---

## Struktur berpikir saat mengembangkan fitur

Setiap perubahan idealnya bisa dijawab dengan urutan ini:

1. masalah bisnis apa yang sedang diselesaikan?
2. domain mana yang menjadi source of truth?
3. invariant apa yang tidak boleh rusak?
4. use case mana yang harus mengorkestrasi flow?
5. adapter mana yang boleh tahu detail framework / database?
6. bukti verifikasi apa yang memastikan perubahan ini benar?

Bila urutan itu tidak bisa dijawab, implementasi belum cukup matang.

---

## Roadmap besar project

Roadmap domain project dikunci agar pertumbuhan fitur tetap tertib.

### Fase 0 — istilah dan rule domain
Mengunci istilah UI final, status, dan aturan lifecycle dasar.

### Fase 1 — UI nota-centric
UI diarahkan agar mengikuti model domain Nota sebagai pusat interaksi.

### Fase 2 — satu nota aktif per pelanggan
Menegaskan batasan operasional yang diperlukan.

### Fase 3 — payment lifecycle
Partial payment dibuat eksplisit sebagai model final.

### Fase 4 — refund lifecycle
Refund dipisahkan sebagai lifecycle yang sah dan tegas.

### Fase 5 — barang / harga
Memperkuat product master dan aturan harga.

### Fase 6 — suplai / avg_cost
Membangun dasar stok masuk dan biaya.

### Fase 7 — laporan
Laporan membaca domain final yang sudah rapih.

### Fase 8 — hardening Telegram / PDF
Ekspansi kanal dilakukan tanpa membongkar core domain.

---

## Cocok untuk siapa?

Project ini cocok untuk tim yang menginginkan:

- sistem kasir yang tidak berhenti di demo UI
- domain yang bisa tumbuh
- struktur code yang bisa diaudit
- operasi yang bergantung pada stok dan transaksi yang benar
- fondasi yang siap diperluas ke kanal lain

Project ini bukan dibangun dengan filosofi “asal jalan dulu”.
Project ini dibangun dengan filosofi “harus benar dari core-nya”.

---

## Nilai utama project ini

Kalau README ini harus diringkas menjadi beberapa nilai utama, maka project ini berdiri di atas prinsip berikut:

- **jelas istilahnya**
- **jelas source of truth-nya**
- **jelas lifecycle-nya**
- **jelas boundary-nya**
- **jelas bukti selesainya**

Dengan kata lain, Kasir V2 bukan hanya project kasir.
Kasir V2 adalah usaha membangun **sistem operasional yang bisa dipercaya**.

---

## Ringkasan satu paragraf

**Kasir V2** adalah web admin kasir berbasis domain yang menempatkan **products**, **inventory**, **supplier invoices**, **Nota/Kasus/Rincian**, dan **reporting** ke dalam kontrak arsitektur yang jelas. Project ini memakai **hexagonal architecture**, workflow **blueprint-first**, eksekusi **step-by-step**, dan **Definition of Done** berbasis bukti, agar setiap fitur baru tetap tunduk pada source of truth yang sama dan fondasi sistem tetap stabil saat berkembang ke Telegram bot, PDF, dan kebutuhan operasional berikutnya.

---

## Status project

Project ini berada dalam pengembangan aktif dengan fokus pada penguncian fondasi domain dan kualitas eksekusi.
Prioritas utamanya bukan menambah fitur sebanyak mungkin, tetapi memastikan setiap fitur yang masuk:
- benar secara domain
- rapi secara arsitektur
- bisa diverifikasi
- aman untuk dijadikan pijakan fase berikutnya

---

## Penutup

Kalau seseorang membaca README ini saja, mereka harus langsung paham bahwa project ini:

- adalah **project kasir/backoffice**
- dibangun dengan **pendekatan domain-first**
- memakai **hexagonal architecture**
- punya **source of truth yang tegas**
- menjaga **stok, transaksi, suplai, dan laporan** secara disiplin
- dan dikembangkan dengan **proses kerja berbasis bukti**, bukan sekadar asumsi

Itulah identitas Kasir V2.
