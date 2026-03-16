# Handoff — Step 10 Employee Finance

## Metadata
- Tanggal: 2026-03-17
- Nama slice / topik: Step 10 — Employee Finance
- Workflow step: Step 10
- Status: IMPLEMENTED, PARTIALLY VERIFIED
- Progres: 90%

## Target halaman kerja
Menertibkan hasil implementasi Step 10 Employee Finance agar kembali sinkron dengan workflow resmi dan hasil audit repo, tanpa membangun ulang bounded context.

Target spesifik yang telah diaudit:
- employee registration
- payroll manual
- payroll mode harian/mingguan/bulanan
- employee debt
- debt payment

## Referensi yang dipakai `[REF]`
- Blueprint:
  - `docs/blueprint/blueprint_v1.md`
- Workflow:
  - `docs/workflow/workflow_v1.md`
- DoD:
  - `docs/dod/dod_v1.md`
- ADR:
  - `docs/adr/0013-employee-finance-foundation.md`
- Handoff sebelumnya:
  - `docs/handoff/handoff_step_9_correction_refund_audit.md`
- Snapshot repo / output command yang dipakai:
  - `tree -L9 app database docs mk resources routes scripts tests`
  - `php artisan test tests/Feature/EmployeeFinance`
  - `php artisan test tests/Arch`
  - `php artisan test`
  - `php -l app/Application/EmployeeFinance/UseCases/DisbursePayrollHandler.php`
  - `php -l app/Application/EmployeeFinance/UseCases/PayEmployeeDebtHandler.php`
  - `php -l app/Application/EmployeeFinance/UseCases/UpdateEmployeeBaseSalaryHandler.php`
  - `php -l app/Core/EmployeeFinance/Employee/Employee.php`
  - `php -l app/Core/EmployeeFinance/Employee/PayPeriod.php`
  - `php -l app/Core/EmployeeFinance/Payroll/DisbursementMode.php`
  - `make verify`
  - `grep -R -n "DisbursementMode::MANUAL\|manual'\|manual\"" app tests database routes docs 2>/dev/null`

## Fakta terkunci `[FACT]`
- Workflow resmi Step 10 mewajibkan:
  - employee
  - payroll manual
  - payroll mode harian/mingguan/bulanan
  - employee debt
  - debt payment
- Struktur Step 10 sudah masuk ke repo lintas layer:
  - Core
  - Application
  - Ports
  - Adapters
  - migrations
  - routes
- `tests/Arch` lulus setelah audit.
- `make verify` lulus setelah audit.
- `php artisan test tests/Feature/EmployeeFinance` lulus untuk:
  - `RegisterEmployeeFeatureTest`
  - `RecordEmployeeDebtFeatureTest`
- `php artisan test` lulus secara umum, dengan 1 risky test di area Payment yang bukan blocker khusus Step 10.
- Handoff Gemini sebelumnya tidak valid sebagai closure final karena:
  - mengklaim 100%
  - memakai bukti test yang diasumsikan
  - tidak sinkron dengan ADR dan workflow
- ADR Gemini sebelumnya juga tidak valid sebagai kontrak final karena:
  - status masih `Proposed`
  - istilah payroll belum sinkron
  - mencampur `manual` sebagai mode
- Hasil audit kode menunjukkan Step 10 tidak perlu dibangun ulang.
- Patch audit yang sudah diterapkan:
  - nominal payroll wajib `> 0`
  - `manual` dihapus dari `DisbursementMode`
  - request payroll hanya menerima `daily|weekly|monthly`
  - `PayPeriod` ditambah `daily`
  - request register salary diubah menjadi `min:1`
  - request update salary diubah menjadi `min:1`
  - domain `Employee` sekarang menolak base salary `<= 0`

## Scope yang dipakai
### `[SCOPE-IN]`
- Audit struktur repo Step 10 berdasarkan tree.
- Audit handler:
  - `RegisterEmployeeHandler`
  - `UpdateEmployeeBaseSalaryHandler`
  - `PayEmployeeDebtHandler`
  - `DisbursePayrollHandler`
- Audit domain:
  - `Employee`
  - `EmployeeDebt`
  - `DebtPayment`
  - `PayrollDisbursement`
  - `PayPeriod`
  - `DisbursementMode`
- Audit request:
  - `RegisterEmployeeRequest`
  - `UpdateEmployeeBaseSalaryRequest`
  - `PayEmployeeDebtRequest`
  - `DisbursePayrollRequest`
- Sinkronisasi kontrak kode terhadap workflow resmi Step 10.
- Verifikasi syntax, arch test, feature test EmployeeFinance, dan `make verify`.

### `[SCOPE-OUT]`
- Menambahkan fitur baru di luar workflow Step 10.
- Auto payroll / cron payroll.
- Timesheet / absensi.
- Read model reporting employee finance.
- Menutup gap test baru untuk:
  - `PayEmployeeDebt`
  - `DisbursePayroll`
  - `UpdateEmployeeBaseSalary`
- Rewrite total bounded context Employee Finance.

## Keputusan yang dikunci `[DECISION]`
- Source of truth untuk Step 10 tetap:
  - workflow
  - blueprint
- Handoff dan ADR hasil Gemini harus direvisi, bukan dijadikan acuan final.
- Employee Finance tidak perlu dibangun ulang; cukup perbaikan kecil terarah.
- `manual` bukan nilai enum payroll mode.
- Payroll mode yang sah untuk Step 10:
  - `daily`
  - `weekly`
  - `monthly`
- Penggajian tetap manual sebagai cara pencatatan/admin input, bukan sebagai enum mode.
- Base salary wajib lebih dari nol.
- Penurunan gaji pokok wajib menyertakan alasan.
- Debt payment wajib lebih dari nol.
- Debt payment tidak boleh melebihi sisa hutang.
- Hutang yang sudah lunas tidak boleh menerima pembayaran lagi.
- Step 10 belum boleh ditutup 100% sebelum proof untuk handler berikut benar-benar ada:
  - `PayEmployeeDebt`
  - `DisbursePayroll`
  - `UpdateEmployeeBaseSalary`

## File yang dibuat/diubah `[FILES]`

### File diubah saat audit
- `app/Core/EmployeeFinance/Payroll/PayrollDisbursement.php`
- `app/Core/EmployeeFinance/Payroll/DisbursementMode.php`
- `app/Core/EmployeeFinance/Employee/Employee.php`
- `app/Core/EmployeeFinance/Employee/PayPeriod.php`
- `app/Adapters/In/Http/Requests/EmployeeFinance/DisbursePayrollRequest.php`
- `app/Adapters/In/Http/Requests/EmployeeFinance/UpdateEmployeeBaseSalaryRequest.php`
- `app/Adapters/In/Http/Requests/EmployeeFinance/RegisterEmployeeRequest.php`
- `tests/Feature/EmployeeFinance/RegisterEmployeeFeatureTest.php`
- `database/migrations/2026_03_16_000500_create_payroll_disbursements_table.php`
- `docs/handoff/handoff_step_10_employee_finance.md`
- `docs/adr/0013-employee-finance-foundation.md`

## Bukti verifikasi `[PROOF]`
- command:
  - `php artisan test tests/Feature/EmployeeFinance`
  - hasil:
    - `RecordEmployeeDebtFeatureTest` pass
    - `RegisterEmployeeFeatureTest` pass
- command:
  - `php artisan test tests/Arch`
  - hasil:
    - `HexagonalDependencyTest` pass
- command:
  - `make verify`
  - hasil:
    - phpstan pass
    - audit line count pass
- command:
  - `php artisan test`
  - hasil:
    - suite pass
    - terdapat `1 risky` di area Payment, bukan blocker khusus Step 10
- command:
  - `grep -R -n "DisbursementMode::MANUAL\|manual'\|manual\"" app tests database routes docs 2>/dev/null`
  - hasil:
    - referensi `manual` yang bocor berhasil diinventarisasi dan dibersihkan dari kontrak mode payroll

## Blocker aktif
- Belum ada feature test spesifik untuk:
  - `PayEmployeeDebtHandler`
  - `DisbursePayrollHandler`
  - `UpdateEmployeeBaseSalaryHandler`
- Karena itu Step 10 belum layak diberi status `100% closed`.

## State repo yang penting untuk langkah berikutnya
- Bounded context Employee Finance tetap dipertahankan.
- Perbaikan audit sudah mengunci rule penting payroll, debt payment, dan base salary.
- Struktur besar Step 10 tidak perlu rewrite.
- Langkah berikut paling aman adalah menutup gap proof untuk 3 handler yang belum punya feature test eksplisit.

## Next step paling aman `[NEXT]`
- Tambahkan feature test untuk:
  - `PayEmployeeDebtHandler`
  - `DisbursePayrollHandler`
  - `UpdateEmployeeBaseSalaryHandler`

## Catatan masuk halaman berikutnya
Bila Step 10 ingin ditutup penuh, bawa minimal:
- handoff ini
- `docs/workflow/workflow_v1.md`
- `docs/blueprint/blueprint_v1.md`
- `docs/adr/0013-employee-finance-foundation.md`
- bukti test terbaru

## Ringkasan singkat siap tempel

### Ringkasan
- target: audit dan sinkronisasi hasil implementasi Step 10 Employee Finance.
- status: IMPLEMENTED, PARTIALLY VERIFIED.
- progres: 90%.
- hasil utama:
  - struktur Step 10 aman
  - tidak perlu rewrite
  - kontrak payroll/base salary/pay period sudah dirapikan
  - closure Gemini lama dinyatakan tidak valid
- next step:
  - tambah proof test untuk pay debt, disburse payroll, dan update salary.

### Jangan dibuka ulang
- Jangan hidupkan kembali `manual` sebagai enum payroll mode.
- Jangan turunkan base salary ke `0`.
- Jangan menutup Step 10 sebagai `100%` tanpa bukti test handler yang belum ada.

### Data minimum bila ingin lanjut
- `docs/handoff/handoff_step_10_employee_finance.md`
- `docs/adr/0013-employee-finance-foundation.md`
- output test terbaru
