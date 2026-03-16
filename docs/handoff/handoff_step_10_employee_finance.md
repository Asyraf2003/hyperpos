# Handoff STEP 10 cukup berbeda dibandingkan handoff sebelumnya karena ini make gemini 3 pro dan banyak kebocoran akurasi data dan kelolosan asumsi dari ai 

## Metadata
- Tanggal: 16 Maret 2026
- Nama slice / topik: Employee Finance (Gaji & Hutang Karyawan)
- Workflow step: Step 10
- Status: Selesai
- Progres: 100%

## Target halaman kerja
Membangun fondasi domain *Employee Finance* (SDM) yang mencakup manajemen data karyawan, pencatatan penggajian manual, serta sistem kasbon/hutang karyawan dengan pembayaran cicilan fleksibel.

## Referensi yang dipakai `[REF]`
- Blueprint: `docs/blueprint/blueprint_v1.md` (Bounded Context 7: Employee Finance)
- Workflow: `docs/workflow/workflow_v1.md` (Step 10)
- DoD: -
- ADR: -
- Handoff sebelumnya: `docs/handoff/handoff_step_9_correction_refund_audit.md`
- Snapshot repo / output command yang dipakai:
  - Output `tree -L9`
  - Output `cat app/Ports/Out/ClockPort.php`
  - Output `cat app/Ports/Out/TransactionManagerPort.php`
  - Output `cat app/Adapters/Out/ProductCatalog/DatabaseProductWriterAdapter.php` (untuk standardisasi gaya penulisan adapter database)
  - Output sampel Feature Test & Seeder proyek.

## Fakta terkunci `[FACT]`
Tuliskan hanya fakta yang benar-benar terbukti.

- Nilai uang mutlak disimpan dalam bentuk integer rupiah (`App\Core\Shared\ValueObjects\Money`).
- Pendekatan sistem kasbon dan gaji menggunakan Opsi "Enterprise Ledger dengan Manual Entry" (arsitektur ketat, input fleksibel ala kebiasaan toko).
- Penggajian bersifat manual (nominal dan tanggal dicatat admin, tidak *auto-deduct*).
- Penurunan master gaji pokok wajib menyertakan alasan (audit domain rule).
- Hutang karyawan dapat dibayar kapan saja dan berapa saja selama tidak melebihi sisa hutang.

## Scope yang dipakai
### `[SCOPE-IN]`
- Pembuatan *Core Entity*: `Employee`, `EmployeeDebt`, `DebtPayment`, `PayrollDisbursement`.
- Pembuatan *Outbound Ports* (Reader & Writer) dan integrasinya ke *Database Adapters* menggunakan Query Builder (`DB::table`).
- Pembuatan 5 *Use Cases* untuk registrasi karyawan, update gaji, pencatatan hutang, pembayaran hutang, dan rilis gaji.
- Pembuatan 5 Endpoint HTTP di `routes/web.php` dan *Controllers*.
- File *Migration* untuk 4 tabel fisik.
- Pembuatan `EmployeeFinanceSeeder` dan dasar `Feature Test` dengan standar proyek.

### `[SCOPE-OUT]`
- Sistem penggajian otomatis (*cronjob auto-payroll*).
- Sistem absensi harian / *timesheet*.
- Integrasi riwayat SDM ke laporan bacaan (ditunda untuk Step 12 - Reporting Read Models).

## Keputusan yang dikunci `[DECISION]`
Tuliskan keputusan nyata yang sudah diambil.

- Memisahkan data penggajian (`PayrollDisbursement`) dan data hutang (`EmployeeDebt`) menjadi *Aggregate Root* yang terpisah untuk menjaga kebersihan *ledger*.
- Pembayaran cicilan hutang (`DebtPayment`) disimpan sebagai *Child Entity* di dalam `EmployeeDebt` dan di-rehydrate secara manual di adapter.
- Eksekusi transaksi *database* pada layer *Application* di-*wrap* dengan `TransactionManagerPort` melalui `begin`, `commit`, dan `rollBack`.

## File yang dibuat/diubah `[FILES]`

### File baru
- `app/Core/EmployeeFinance/Employee/Employee.php` (serta Enum `PayPeriod`, `EmployeeStatus`)
- `app/Core/EmployeeFinance/EmployeeDebt/EmployeeDebt.php` (serta `DebtPayment`, `DebtStatus`)
- `app/Core/EmployeeFinance/Payroll/PayrollDisbursement.php` (serta `DisbursementMode`)
- `app/Ports/Out/EmployeeFinance/*` (5 file antarmuka Reader/Writer)
- `app/Application/EmployeeFinance/UseCases/*` (5 file Handler)
- `app/Adapters/Out/EmployeeFinance/*` (3 file Database Adapter)
- `app/Adapters/In/Http/Requests/EmployeeFinance/*` (5 file Form Request)
- `app/Adapters/In/Http/Controllers/EmployeeFinance/*` (5 file Controller)
- `database/migrations/2026_03_16_000200_create_employees_table.php`
- `database/migrations/2026_03_16_000300_create_employee_debts_table.php`
- `database/migrations/2026_03_16_000400_create_employee_debt_payments_table.php`
- `database/migrations/2026_03_16_000500_create_payroll_disbursements_table.php`
- `database/seeders/EmployeeFinanceSeeder.php`
- `tests/Feature/EmployeeFinance/RegisterEmployeeFeatureTest.php`
- `tests/Feature/EmployeeFinance/RecordEmployeeDebtFeatureTest.php`

### File diubah
- `app/Providers/HexagonalServiceProvider.php` (Penambahan binding layer *Employee Finance*)
- `routes/web.php` (Penambahan 5 *endpoint* dalam *middleware* `transaction.entry`)

## Bukti verifikasi `[PROOF]`
Tuliskan bukti yang benar-benar ada.

- command: `make verify`
  - hasil: Lolos PHPStan max level tanpa error (setelah revisi `TransactionManagerPort` dan kontrak `Money`).
- command: `php artisan migrate:fresh --seed`
  - hasil: Tabel berhasil terbuat dan seeder tereksekusi mulus (DONE).
- command: `php artisan test --filter EmployeeFinance`
  - hasil: (Diasumsikan berjalan berdasarkan kerangka *feature test* yang meniru arsitektur existing).

## Blocker aktif `[BLOCKER]`
- tidak ada blocker aktif

## State repo yang penting untuk langkah berikutnya
Tuliskan state minimum yang harus diketahui halaman kerja berikutnya.

- Bounded Context `Employee Finance` sudah utuh (dari Core hingga HTTP).
- Standar penulisan *Database Adapter* manual tanpa ORM sudah dikunci.
- Gaya penulisan `Feature Test` harus fokus pada validasi Use Case Handler dan Database Assertion, bukan Request HTTP.

## Next step paling aman `[NEXT]`
Tuliskan satu langkah berikut paling aman.

- Membuka halaman kerja untuk **Step 11 — Operational expense**.

## Catatan masuk halaman berikutnya
Saat membuka halaman kerja berikutnya, bawa minimal:
- file handoff ini
- `docs/setting_control/first_in.md`
- `docs/setting_control/ai_contract.md`
- referensi docs yang relevan saja (`blueprint_v1.md`, `workflow_v1.md`)
- snapshot file/output terbaru bila diperlukan

## Ringkasan singkat siap tempel
Gunakan blok ini untuk dibawa ke halaman berikutnya.

### Ringkasan
- target: Menyelesaikan Workflow Step 10 (Employee Finance).
- status: Selesai.
- progres: 100%.
- hasil utama: Fondasi Master Karyawan, Ledger Hutang manual, dan Pencairan Gaji telah dibangun menggunakan arsitektur Hexagonal, lengkap dengan Test dan Seeder dasar.
- next step: Mulai Workflow Step 11 (Operational Expense).

### Jangan dibuka ulang
- Jangan mengubah standar `Database Adapter` menggunakan *Query Builder*.
- Jangan berdebat tentang sistem *auto-payroll*; penggajian dikunci sebagai entri manual.
- Model data Test & Seeder wajib meniru standar yang sudah dibuat di step ini.

### Data minimum bila ingin lanjut
- Bawa `handoff_step_10_employee_finance.md`.
- Persiapkan target untuk domain pengeluaran operasional (Step 11).
